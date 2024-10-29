<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationStoreRequest;
use App\Http\Requests\LoanApplicationUpdateRequest;
use App\Interface\Service\FeeServiceInterface;
use App\Interface\Service\LoanApplicationCoMakerServiceInterface;
use App\Interface\Service\LoanApplicationFeeServiceInterface;
use App\Interface\Service\LoanApplicationServiceInterface;
use App\Interface\Service\LoanReleaseServiceInterface;
use App\Interface\Service\PaymentLineServiceInterface;
use App\Interface\Service\PaymentScheduleServiceInterface;
use App\Interface\Service\PaymentServiceInterface;
use App\Models\Customer;
use App\Models\Document_Status_Code;
use App\Models\Factor_Rate;
use App\Models\Fees;
use App\Models\Holiday;
use App\Models\Loan_Application;
use App\Models\Loan_Application_Comaker;
use App\Models\Loan_Application_Fees;
use App\Models\Loan_Count;
use App\Models\Loan_Release;
use App\Models\Payment_Duration;
use App\Models\Payment_Frequency;
use App\Models\Payment_Schedule;
use App\Service\LoanApplicationFeeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LoanApplicationController extends Controller
{

    private $loanApplicationService;
    public function __construct(LoanApplicationServiceInterface $loanApplicationService)
    {
        $this->loanApplicationService = $loanApplicationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(LoanApplicationFeeServiceInterface $loanApplicationFeeService, LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService)
    {
        $loanFees = $loanApplicationFeeService->findLoanFees();
        $loanCoMakers = $loanApplicationCoMakerService->findCoMakers();
        $loanApps = $this->loanApplicationService->findLoanApplication();

        $loanAppsMap = [];
        // Create a map of loan applications using the loan application ID as the key
        foreach ($loanApps as $loanApp) {
            $loanAppsMap[$loanApp->id] = [
                'Loan_Application' => $loanApp,
                'Fees' => [],  // Initialize an empty array to hold fees for each loan application
                'CoMaker' => '',
            ];
        }

        // Group loan fees under the corresponding loan application
        foreach ($loanFees as $loanFee) {
            $loanAppId = $loanFee->loan_application_id;

            // Check if the loan application exists in the map
            if (isset($loanAppsMap[$loanAppId])) {
                // Add the fee to the corresponding loan application
                $loanAppsMap[$loanAppId]['Fees'][] = $loanFee;
            }
        }

        foreach ($loanCoMakers as $loanCoMaker)
        {
            $loanAppId = $loanCoMaker->loan_application_id;

            //Check if the loan application exists in the map
            if(isset($loanAppsMap[$loanAppId]))
            {
                // Add the coMaker to the corresponding loan application
                $loanAppsMap[$loanAppId]['CoMaker'] = $loanCoMaker;
            }
        }

        // Convert the map to an array
        $loanAppsWithLoanFees = array_values($loanAppsMap);

        return [
            'data' => $loanAppsWithLoanFees
        ];

    }

    public function store(Request $request, PaymentScheduleServiceInterface $paymentScheduleService, LoanApplicationFeeController $loanApplicationFeeController, LoanApplicationCoMakerController $loanApplicationCoMakerController)
    {
        //return response()->json(['message' => 'hello'], Response::HTTP_INTERNAL_SERVER_ERROR);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $userId = auth()->user()->id;
            $data = $request->input('allCustomerData');  // Assuming 'allCustomerData' is an array

            // Loop through each customer data
            for ($i = 0; $i < count($data); $i++) {
                // Check for existing pending loans for the current customer
                $pendingLoanExists = Loan_Application::where('customer_id', $data[$i]['customer_id'])
                    ->where('document_status_code', 'PENDING')
                    ->exists();

                if ($pendingLoanExists) {
                    throw new \Exception('Cannot create a new loan application. There is already a pending loan for this customer.');
                }

                // Find the group id
                $groupDatas = Customer::where('group_id', $data[0]['group_id'])->get();

                for ($j = 0; $j < count($groupDatas); $j++) {
                    // Fetch total due and total paid for the specific customer
                    $totals = Payment_Schedule::where('customer_id', $groupDatas[$j]['id'])
                    ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'FORWARDED'])
                    ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance')
                    ->first();

                    $balance = $totals->balance ?? 0; // Set default balance to 0

                    return response()->json(['message' => $totals], Response::HTTP_INTERNAL_SERVER_ERROR);

                    if (is_null($totals) && $balance > 0) {
                        throw new \Exception('There still member has not yet fully paid!');
                    }
                }

                // Check if the customer data count is valid
                if (count($data) < 4) {
                    return response()->json([
                        'error' => 'The amount of customers should be at least 4',
                        'message' => 'The amount of customers should be at least 4',
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Check if the coMaker has remaining balance
                if (isset($data[$i]['coMaker']) && $data[$i]['coMaker'] > 0) {
                    $totals = Payment_Schedule::where('customer_id', $data[$i]['coMaker'])
                        ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'FORWARDED'])
                        ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance')
                        ->first();

                    if ($totals && $totals->balance > 0) {
                        throw new \Exception($data[$i]['customer_id'] . ' The coMaker has not yet fully paid!');
                    }
                }

                // Loan count logic
                $loanCountId = Customer::where('id', $data[$i]['customer_id'])->first()->loan_count;
                $customerLoanAmount = $data[$i]['amount_loan'];
                $min = Loan_Count::where('id', $loanCountId)->first()->min_amount;
                $max = Loan_Count::where('id', $loanCountId)->first()->max_amount;

                // Check loan amount validity
                if (($customerLoanAmount < $min) || ($customerLoanAmount > $max)) {
                    return response()->json([
                        'message' => 'The customer loan amount is invalid',
                    ], Response::HTTP_CONFLICT);
                }

                // Prepare data for loan application
                $data[$i]['document_status_code'] = Document_Status_Code::where('description', $data[$i]['document_status_code'])->first()->id;
                $data[$i]['datetime_prepared'] = now();
                $data[$i]['prepared_by_id'] = $userId;
                $data[$i]['last_modified_by_id'] = $userId;

                // Convert into object
                $payload = new Request($data[$i]);

                // Insert the loan application
                $this->loanApplicationService->createLoanApplication($payload);

                // Handle coMaker
                if (isset($data[$i]['coMaker']) && $data[$i]['coMaker'] > 0) {
                    $loanId = Loan_Application::where('loan_application_no', $data[$i]['loan_application_no'])->first()->id;

                    // Store the coMaker
                    $coMaker = [
                        'loan_application_id' => $loanId,
                        'customer_id' => $data[$i]['customer_id'],
                    ];

                    $loanApplicationCoMakerController->store(new Request($coMaker));
                }

                // Process fees
                for ($k = 0; $k < count($data[$i]['fees']); $k++) {
                    $amount = Fees::where('id', $data[$i]['fees'][$k])->first()->amount;
                    $loanId = Loan_Application::where('loan_application_no', $data[$i]['loan_application_no'])->first()->id;

                    // Prepare fees data
                    $fees = [
                        'loan_application_id' => $loanId,
                        'fee_id' => $data[$i]['fees'][$k],
                        'amount' => $amount,
                    ];

                    // Prepare payment schedule payload
                    $payload = [
                        'customer_id' => $data[$i]['customer_id'],
                        'loan_released_id' => null,
                        'datetime_due' => now(),
                        'amount_due' => $amount,
                        'amount_interest' => 0, // Assuming equal interest distribution
                        'amount_paid' => 0,
                        'payment_status_code' => 'UNPAID', // Default status
                        'remarks' => 'FEES',
                    ];

                    // Create payment schedule entry
                    $paymentScheduleService->createPaymentSchedule(new Request($payload));

                    // Store the fees
                    $loanApplicationFeeController->store(new Request($fees));
                }
            }

            // If all is good, commit the transaction
            DB::commit();

            // Return a success message
            return response()->json([
                'message' => 'Loan applications and fees successfully inserted',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback the transaction if something went wrong
            DB::rollBack();

            // Return an error message
            return response()->json([
                'error' => 'An error occurred while processing the transaction',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(int $id, LoanApplicationFeeServiceInterface $loanApplicationFeeService, LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService)
    {
        // Find loan application by user ID
        $loanApp = $this->loanApplicationService->findLoanApplicationById($id);

        if (!$loanApp) {
            return response()->json(['error' => 'Loan Application not found'], 404);
        }

        // Get the loan_application_id from the loan application
        $loanApplicationId = $loanApp->id;

        // Find fees associated with the loan application ID
        $loanFees = Loan_Application_Fees::where('loan_application_id', $loanApplicationId)->get();
        // Find co-makers associated with the loan application ID
        $loanCoMakers = Loan_Application_Comaker::where('loan_application_id', $loanApplicationId)->get();

        // Format the result as needed
        $loanData = [
            'loan_applications' => $loanApp,     // Loan application details
            'fees' => $loanFees,           // Loan fees array
            'comakers' => $loanCoMakers    // Loan co-makers array
        ];

        return response()->json([
            'data' => $loanData
        ]);
    }

    public function look(string $id, LoanApplicationFeeServiceInterface $loanApplicationFeeService, LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService)
    {
        // Find loan application by user ID
        $loanApp = Loan_Application::where('loan_application_no', $id)->first();

        if (!$loanApp) {
            return response()->json(['error' => 'Loan Application not found'], 404);
        }

        // Get the loan_application_id from the loan application
        $loanApplicationId = $loanApp->id;

        // Find fees associated with the loan application ID
        $loanFees = Loan_Application_Fees::where('loan_application_id', $loanApplicationId)->get();
        // Find co-makers associated with the loan application ID
        $loanCoMakers = Loan_Application_Comaker::where('loan_application_id', $loanApplicationId)->get();

        $factorRate = Factor_Rate::where('id', $loanApp->factor_rate)->first();

        $loanApp->factor_rate_value = $factorRate->value;

        foreach ($loanFees as $loanFee)
        {
            $Fee = Fees::where('id', $loanFee->fee_id)->first();

            $loanFee->description = $Fee->description;
        }

        //return response()->json(['message' => $Fee], Response::HTTP_INTERNAL_SERVER_ERROR);
        // Format the result as needed
        $loanData = [
            'loan_applications' => $loanApp,     // Loan application details
            'fees' => $loanFees,
            'comakers' => $loanCoMakers,
        ];

        return response()->json([
            'data' => $loanData
        ]);

    }

    public function see(string $id, LoanApplicationFeeServiceInterface $loanApplicationFeeService, LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService)
    {
        // Find loan application by user ID
        $loanApp = Loan_Application::where('customer_id', $id)->get();

        if (!$loanApp) {
            return response()->json(['error' => 'Loan Application not found'], 404);
        }

        return response()->json([
            'data' => $loanApp,
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id, LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService, LoanApplicationFeeServiceInterface $loanApplicationFeeService)
{
    $userId = auth()->user()->id;
    $request['last_modified_by_id'] = $userId;
    $fees = $request['fees'];

    $payload = [
        'loan_application_id' => $request['id'],
        'customer_id' => $request['co_maker_id'],
    ];

    $payload = new Request($payload);

    //find the comaker id
    $oldCoMakerId = Loan_Application_Comaker::where('loan_application_id', $request['id'])->first()->id;

    // Update the loan application itself
    $this->loanApplicationService->updateLoanApplication($request, $id);

    $loanApplicationCoMakerService->updateLoanCoMaker($payload, $oldCoMakerId);

    // Fetch existing fees for the loan application
    $existingFees = Loan_Application_Fees::where('loan_application_id', $id)->get();

    // Prepare an array to track the fee IDs that are being processed
    $feeIdsToProcess = [];

    // Loop through incoming fees to update or create
    foreach ($fees as $fee) {
        $feeIdsToProcess[] = $fee['fee_id']; // Collect fee IDs for later comparison

        // Check if the fee already exists
        $existingFee = $existingFees->firstWhere('fee_id', $fee['fee_id']);

        if ($existingFee) {
            // Update existing fee
            $existingFee->amount = $fee['amount'];
            $existingFee->save();
        } else {
            // Create new fee record
            Loan_Application_Fees::create([
                'loan_application_id' => $id,
                'fee_id' => $fee['fee_id'],
                'amount' => $fee['amount'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // Remove fees that were not included in the update
    foreach ($existingFees as $existingFee) {
        if (!in_array($existingFee->fee_id, $feeIdsToProcess)) {
            $existingFee->delete();
        }
    }

    return response()->json([
        'message' => 'Loan application fees updated successfully.',
    ], Response::HTTP_OK);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->loanApplicationService->deleteLoanApplication( $id);
    }

    public function approve(Request $request, int $id, LoanApplicationServiceInterface $loanApplicationService, LoanReleaseServiceInterface $loanReleaseService, PaymentScheduleServiceInterface $paymentScheduleService)
{
    // Helper function to check if a date is a holiday
    function isHoliday($date) {
        return Holiday::where('date', $date->toDateString())
                      ->where('isActive', 1)
                      ->exists();
    }

    // Helper function to adjust due date for Sundays and holidays
    function adjustDueDate($dueDate) {
        // Loop until due date is neither a Sunday nor a holiday
        while ($dueDate->isSunday() || isHoliday($dueDate)) {
            $dueDate->addDay();
        }
        return $dueDate;
    }

    // Start the transaction
    DB::beginTransaction();

    try {
        $userId = auth()->user()->id;
        $customerId = $request['customer_id'];
        $loanId = $request['id'];

        $payableFee = Payment_Schedule::where('customer_id', $customerId)
            ->where(function($query) {
                $query->where('remarks', 'FEES')
                      ->orWhere('remarks', 'PARTIALLY PAID');
            })
            ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED'])
            ->selectRaw('SUM(amount_due) - SUM(amount_paid) as fee_balance')
            ->first();

        if ($payableFee && $payableFee->fee_balance > 0) {
            throw new \Exception('Cannot approve: outstanding fees need to be paid');
        }

        $loanApproveId = Document_Status_Code::where('description', 'Approved')->first()->id;

        $loanApplication = Loan_Application::findOrFail($loanId);
        $loanApplication->document_status_code = $loanApproveId;
        $loanApplication->save();
        $loanApplication->fresh();

        $passbookNo = Customer::where('id', $customerId)->first()->passbook_no;

        $loanAmount = $request['amount_loan'];
        $factorRateId = $request['factor_rate'];
        $amountInterest = $request['amount_interest'];

        $factorRate = Factor_Rate::findOrFail($factorRateId);
        $paymentFrequencyId = $factorRate->payment_frequency_id;
        $paymentDurationId = $factorRate->payment_duration_id;

        $paymentFrequency = Payment_Frequency::findOrFail($paymentFrequencyId);
        $paymentDuration = Payment_Duration::findOrFail($paymentDurationId);

        $loanReleasePayload = [
            'datetime_prepared' => now(),
            'passbook_number' => $passbookNo,
            'loan_application_id' => $loanId,
            'prepared_by_id' => $userId,
            'amount_loan' => $loanAmount,
            'amount_interest' => $amountInterest,
            'datetime_first_due' => now()->addDays($paymentFrequency->days_interval),
            'notes' => $request->get('notes', null),
        ];

        $loanReleasePayload = new Request($loanReleasePayload);
        $loanRelease = $loanReleaseService->createLoanRelease($loanReleasePayload);

        $numberOfPayments = $paymentDuration->number_of_payments;
        $amountDue = ($loanAmount + $amountInterest) / $numberOfPayments;
        $firstDueDate = $loanReleasePayload['datetime_first_due'];

        for ($i = 0; $i < $numberOfPayments; $i++) {
            $dueDate = adjustDueDate($firstDueDate->copy()->addDays($i * $paymentFrequency->days_interval));

            $payload = [
                'customer_id' => $customerId,
                'loan_released_id' => $loanRelease->id,
                'datetime_due' => $dueDate,
                'amount_due' => $amountDue,
                'amount_interest' => $amountInterest / $numberOfPayments,
                'amount_paid' => 0,
                'payment_status_code' => 'UNPAID',
                'remarks' => null,
            ];

            $payload = new Request($payload);
            $paymentScheduleService->createPaymentSchedule($payload);
        }

        DB::commit();

        return response()->json(['message' => 'Loan release and payment schedule created successfully.'], Response::HTTP_OK);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


public function createPayment(Request $request, PaymentServiceInterface $paymentService, PaymentLineServiceInterface $paymentLineService, PaymentScheduleServiceInterface $paymentScheduleService)
{
    /*
    here will create the payment function
    for payment requirements
    id 	customer_id 	prepared_at 	document_status_code 	prepared_by_id 	amount_paid 	notes 	created_at 	updated_at
    example for database
    id	customer_id	datetime_created	datetime_prepared	document_status_code	prepared_by_id	amount_paid	notes
    1	1001	10/09/2024 18:48	10/09/2024 19:00	APPROVED	20	1000	Payment for loan installment.
    */

    /*
    here will create payment line
    for payment line requirements
    id 	payment_id 	payment_schedule_id 	balance 	amount_paid 	remarks 	created_at 	updated_at
    example for database
    id	payment_id	payment_schedule_id	balance	amount_paid	remarks
    101	1	1001	2000	1000	Partial payment.

    notes
    the purpose of payment line is like a many relation example there could be 1 schedule for 2 payments as partial payment or vice versa
    */

    /*
    here will update the payment schedule
    for payment schedule requirements
    id	customer_id	loan_released_id	datetime_due	amount_due	amount_interest	amount_paid	payment_status_code	remarks	created_at
    example for database
    id	customer_id	loan_released_id	datetime_due	amount_due	amount_interest	amount_paid	payment_status_code	remarks	created_at	updated_at
    2	8	6	2024-10-15 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    3	8	6	2024-10-22 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    4	8	6	2024-10-29 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    5	8	6	2024-11-05 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    6	8	6	2024-11-12 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    7	8	6	2024-11-19 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    8	8	6	2024-11-26 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    9	8	6	2024-12-03 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    10	8	6	2024-12-10 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    11	8	6	2024-12-17 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    12	8	6	2024-12-24 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    13	8	6	2024-12-31 13:07:22	1400.00	150.00	0.00	0	NULL	2024-10-08 13:07:22	2024-10-08 13:07:22
    */

    /*
    Notes
    What I want is to create a function for payment and payment line and update the loan schedule if either the first payment is fully paid then it will mark as PAID
    But if the payment in not enough to be fully paid it will only reflect as partial payment in the payment line and also reflect the payment in payment table
    */
}
}
