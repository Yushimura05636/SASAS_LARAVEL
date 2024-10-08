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
use App\Models\Document_Status_code;
use App\Models\Factor_Rate;
use App\Models\Fees;
use App\Models\Loan_Application;
use App\Models\Loan_Application_Comaker;
use App\Models\Loan_Application_Fees;
use App\Models\Loan_Count;
use App\Models\Payment_Duration;
use App\Models\Payment_Frequency;
use App\Service\LoanApplicationFeeService;
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

public function store(Request $request, LoanApplicationFeeController $loanApplicationFeeController , LoanApplicationCoMakerController $loanApplicationCoMakerController)
{

    $userId = auth()->user()->id;
    $data = $request->input('allCustomerData');  // Assuming 'allCustomerData' is an array

    // Start a database transaction
    DB::beginTransaction();

    try {

        //according kang sir mars recommended ang 8 but minimum of 6
        // if(count($data) < 6 || count($data) > 8)
        // {
        //     return response()->json([
        //         'error' => 'The amount of customers should not be less than 6 or greater than 8',
        //         'message' => 'The amount of customers should not be less than 6 or greater than 8',
        //     ], Response::HTTP_CONFLICT);
        // }



        for ($i = 0; $i < count($data); $i++) {


            // //check for coMakers
            // if($data[$i]['coMaker'])
            // {
            //     //check here if the coMaker has still on going loans and still has balance
            // }

            //continue if there is not problem in CoMaker
            // return response()->json([
            //     $data,
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);

            //for loan count logic
            $loanCountId = Customer::where('id', $data[$i]['customer_id'])->first()->loan_count;
            $customerLoanAmount = $data[$i]['amount_loan'];
            $min = Loan_Count::where('id', $loanCountId)->first()->min_amount;
            $max = Loan_Count::where('id', $loanCountId)->first()->max_amount;

            // if(($customerLoanAmount < $min) || ($customerLoanAmount > $max))
            // {
            //     return response()->json([
            //         'message' => 'The customer loan amount is invalid',
            //     ], Response::HTTP_CONFLICT);
            // }

            // Convert the PENDING name to value integer
            $data[$i]['document_status_code'] = Document_Status_code::where('description', $data[$i]['document_status_code'])->first()->id;

            // Convert the time to now
            $data[$i]['datetime_prepared'] = now();

            //set in which who the user that modify and prepare the ui

            //prepare
            $data[$i]['prepared_by_id'] = $userId;

            //modify
            $data[$i]['last_modified_by_id '] = $userId;

            // Convert into object
            $payload = new Request($data[$i]);

            // return response()->json([
            //     'message' => 'An error occurred while processing the transaction',
            //     'error' => $data[$i]['fees'],
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);
            // Insert the loan application

            $this->loanApplicationService->createLoanApplication($payload);

            $loanId = Loan_Application::where('loan_application_no', $data[$i]['loan_application_no'])->first()->id;

            //here will the store comaker if all is good
            //store the coMaker
            $coMaker = [
                'loan_application_id' => $loanId,
                'customer_id' => $data[$i]['customer_id'],
            ];

            // return response()->json([
            //         'message' => 'An error occurred while processing the transaction',
            //         'error' => $coMaker,
            //     ], Response::HTTP_INTERNAL_SERVER_ERROR);

            $loanApplicationCoMakerController->store(new Request($coMaker));

            // return response()->json([
            //     'message' => 'An error occurred while processing the transaction',
            //     'error' => $coMaker,
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);

            //then save the fees value
            for($k = 0; $k < count($data[$i]['fees']); $k++)
            {


                $amount = Fees::where('id', $data[$i]['fees'][$k])->first()->amount;
                //loan id is here
                $loanId = Loan_Application::where('loan_application_no', $data[$i]['loan_application_no'])->first()->id;

                //convert fees and add amount
                $fees = [
                    'loan_application_id' => $loanId,
                    'fee_id' => $data[$i]['fees'][$k],
                    'amount' => $amount,
                ];

            //     return response()->json([
            //     'message' => 'An error occurred while processing the transaction',
            //     'error' => $fees,
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);

                //store the fees
                $loanApplicationFeeController->store(new Request($fees));
            }


            // Insert the fees here (you can implement this part if needed)

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

        // Log the exception if necessary
        // Log::error($e->getMessage());

        // Return an error message
        return response()->json([
            'message' => 'An error occurred while processing the transaction',
            'error' => $e->getMessage(),
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

    public function approve(Request $request,int $id, LoanReleaseServiceInterface $loanReleaseService, PaymentScheduleServiceInterface $paymentScheduleService)
{
    // Start the transaction
    DB::beginTransaction();

    try {
        $userId = auth()->user()->id;
        $customerId = $request['customer_id'];
        $loanId = $request['id'];

        // Get the passbook number
        $passbookNo = Customer::where('id', $customerId)->first()->passbook_no;

        // Loan details
        $loanAmount = $request['amount_loan'];
        $factorRateId = $request['factor_rate'];
        $amountInterest = $request['amount_interest'];

        // Fetch payment frequency and duration
        $factorRate = Factor_Rate::findOrFail($factorRateId);
        $paymentFrequencyId = $factorRate->payment_frequency_id;
        $paymentDurationId = $factorRate->payment_duration_id;

        $paymentFrequency = Payment_Frequency::findOrFail($paymentFrequencyId);
        $paymentDuration = Payment_Duration::findOrFail($paymentDurationId);

        // Prepare payload for loan release creation
        $loanReleasePayload = [
            'datetime_prepared' => now(),
            'passbook_number' => $passbookNo,
            'loan_application_id' => $loanId,
            'prepared_by_id' => $userId,
            'amount_loan' => $loanAmount,
            'amount_interest' => $amountInterest,
            'datetime_first_due' => now()->addDays($paymentFrequency->days_interval), // Calculate first due date based on frequency
            'notes' => $request->get('notes', null),
        ];

        //custom payload
        $loanReleasePayload = new Request($loanReleasePayload);


        // Save the loan release
        $loanRelease = $loanReleaseService->createLoanRelease($loanReleasePayload);

        // Create payment schedule entries
        $numberOfPayments = $paymentDuration->number_of_payments;
        $amountDue = ($loanAmount + $amountInterest) / $numberOfPayments; // Distributing total amount over payments
        $firstDueDate = $loanReleasePayload['datetime_first_due'];


        for ($i = 0; $i < $numberOfPayments; $i++) {
            // Calculate due date for each payment
            $dueDate = $firstDueDate->copy()->addDays($i * $paymentFrequency->days_interval);

            //custom payload
            $payload = [
                'customer_id' => $customerId,
                'loan_released_id' => $loanRelease->id,
                'datetime_due' => $dueDate,
                'amount_due' => $amountDue,
                'amount_interest' => $amountInterest / $numberOfPayments, // Assuming equal interest distribution
                'amount_paid' => 0,
                'payment_status_code' => 0, // Default status
                'remarks' => null,
            ];


            $payload = new Request($payload);

            // Create payment schedule entry
            $paymentScheduleService->createPaymentSchedule($payload);

            // return response()->json([
            //     'message' => $paymentScheduleService,
            //     'data' => $firstDueDate,
            //     'data 2' => $numberOfPayments,
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Commit the transaction
        DB::commit();

        return response()->json(['message' => 'Loan release and payment schedule created successfully.'], Response::HTTP_OK);

    } catch (\Exception $e) {
        // Rollback the transaction in case of error
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
