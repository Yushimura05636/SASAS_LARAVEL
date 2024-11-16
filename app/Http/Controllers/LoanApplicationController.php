<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationStoreRequest;
use App\Http\Requests\LoanApplicationUpdateRequest;
use App\Interface\Service\CustomerServiceInterface;
use App\Interface\Service\FeeServiceInterface;
use App\Interface\Service\LoanApplicationCoMakerServiceInterface;
use App\Interface\Service\LoanApplicationFeeServiceInterface;
use App\Interface\Service\LoanApplicationServiceInterface;
use App\Interface\Service\LoanReleaseServiceInterface;
use App\Interface\Service\PaymentLineServiceInterface;
use App\Interface\Service\PaymentScheduleServiceInterface;
use App\Interface\Service\PaymentServiceInterface;
use App\Interface\Service\PersonalityServiceInterface;
use App\Models\Credit_Status;
use App\Models\Customer;
use App\Models\Customer_Group;
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
use App\Models\Personality_Status_Map;
use App\Models\User_Account;
use App\Service\LoanApplicationFeeService;
use Carbon\Carbon;
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

     public function index(
        LoanApplicationFeeServiceInterface $loanApplicationFeeService,
        LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService,
        PersonalityServiceInterface $personalityService,
        CustomerServiceInterface $customerService
    ) {
        $loanFees = $loanApplicationFeeService->findLoanFees();
        $loanCoMakers = $loanApplicationCoMakerService->findCoMakers();
        $loanApps = $this->loanApplicationService->findLoanApplication();
        $personalities = $personalityService->findPersonality();
        $customers = $customerService->findCustomers();

        
        // Step 3: Map personalities by their ID for quick lookup
        $personalitiesMap = $personalities->keyBy('id')->map(function ($personality) {
            return [
                'first_name' => $personality->first_name,
                'family_name' => $personality->family_name,
                'middle_name' => $personality->middle_name,
            ];
        });
    
        // Step 4: Map customers by their ID and include the personality details
        $LoanApplicationMap = $customers->keyBy('id')->map(function ($customer) use ($personalitiesMap) {
            $personalityDetails = $personalitiesMap[$customer->personality_id] ?? [];
            return array_merge([
                'customer_id' => $customer->id,
                'first_name' => $customer->first_name,
                'family_name' => $customer->family_name,
                'middle_name' => $customer->middle_name,
            ], $personalityDetails);
        });

        // Loop through loanApps and assign user names
        foreach ($loanApps as $loanApp) {

            $document_status_description = Document_Status_Code::where('id', $loanApp['document_status_code'])->first();

            if(isset($document_status_description) && !is_null($document_status_description))
            {
                $loanApp->document_status_description = strtoupper($document_status_description->description);
            }

            $last_modified_by_user = User_Account::where('id', $loanApp['last_modified_by_id'] ?? null)->first();
            
            if(isset($last_modified_by_user) && !is_null($last_modified_by_user))
            {
                $loanApp->approved_by_user = $last_modified_by_user->last_name . ' '
                . $last_modified_by_user->first_name . ' '
                . $last_modified_by_user->middle_name ?? null;
            }

            $prepared_by_user = User_Account::where('id', $loanApp['prepared_by_id'] ?? null)->first();
            
            if(isset($prepared_by_user) && !is_null($prepared_by_user))
            {
                $loanApp->prepared_by_user = $prepared_by_user->last_name . ' '
                . $prepared_by_user->first_name . ' '
                . $prepared_by_user->middle_name ?? null;
            }

            $released_by_user = User_Account::where('id', $loanApp['released_by_id'] ?? null)->first();
            
            if(isset($released_by_user) && !is_null($released_by_user))
            {
                $loanApp->released_by_user = $released_by_user->last_name . ' '
                . $released_by_user->first_name . ' '
                . $released_by_user->middle_name ?? null;
            }

            $approved_by_user = User_Account::where('id', $loanApp['approved_by_id'] ?? null)->first();
            
            if(isset($approved_by_user) && !is_null($approved_by_user))
            {
                $loanApp->approved_by_user = $approved_by_user->last_name . ' '
                . $approved_by_user->first_name . ' '
                . $approved_by_user->middle_name ?? null;
            }

            $rejected_by_user = User_Account::where('id', $loanApp['rejected_by_id'] ?? null)->first();
            
            if(isset($rejected_by_user) && !is_null($rejected_by_user))
            {
                $loanApp->rejected_by_user = $rejected_by_user->last_name . ' '
                . $rejected_by_user->first_name . ' '
                . $rejected_by_user->middle_name ?? null;
            }

            //return response()->json(['message' => $loanApp], Response::HTTP_INTERNAL_SERVER_ERROR);
            
            // $loanApp->rejected_by_user = isset($userAccounts[$loanApp['rejected_by_user']])
            //     ? ($userAccounts[$loanApp['rejected_by_user']]->last_name . ' ' . 
            //     $userAccounts[$loanApp['rejected_by_user']]->first_name . ' ' . 
            //     $userAccounts[$loanApp['rejected_by_user']]->middle_name)
            //     : null;
            
            // $loanApp->prepared_by_user = isset($userAccounts[$loanApp['prepared_by_user']])
            //     ? ($userAccounts[$loanApp['prepared_by_user']]->last_name . ' ' . 
            //     $userAccounts[$loanApp['prepared_by_user']]->first_name . ' ' . 
            //     $userAccounts[$loanApp['prepared_by_user']]->middle_name)
            //     : null;

            // $loanApp->released_by_user = isset($userAccounts[$loanApp['released_by_user']])
            //     ? ($userAccounts[$loanApp['released_by_user']]->last_name . ' ' . 
            //     $userAccounts[$loanApp['released_by_user']]->first_name . ' ' . 
            //     $userAccounts[$loanApp['released_by_user']]->middle_name)
            //     : null;

            // $loanApp->last_modified_by_user = isset($userAccounts[$loanApp['last_modified_by_user']])
            //     ? ($userAccounts[$loanApp['last_modified_by_user']]->last_name . ' ' . 
            //     $userAccounts[$loanApp['last_modified_by_user']]->first_name . ' ' . 
            //     $userAccounts[$loanApp['last_modified_by_user']]->middle_name)
            //     : null;
            
        }
        
        //return response()->json(['message' => $loanApps], Response::HTTP_INTERNAL_SERVER_ERROR);
    
        // Step 6: Create a map of loan applications, linking to customer and personality data
        $loanAppsMap = [];
        foreach ($loanApps as $loanApp) {
            $customerId = $loanApp->customer_id;
    
            $loanAppsMap[$loanApp->id] = [
                'Loan_Application' => $loanApp,
                'Fees' => [],  // Initialize an empty array to hold fees for each loan application
                'CoMaker' => '',
                'Customer' => $LoanApplicationMap[$customerId] ?? [],  // Add customer and personality details
            ];
        }
    
        // Step 7: Group loan fees under the corresponding loan application
        foreach ($loanFees as $loanFee) {
            $loanAppId = $loanFee->loan_application_id;
    
            if (isset($loanAppsMap[$loanAppId])) {
                $loanAppsMap[$loanAppId]['Fees'][] = $loanFee;
            }
        }
    
        // Step 8: Group co-makers under the corresponding loan application
        foreach ($loanCoMakers as $loanCoMaker) {
            $loanAppId = $loanCoMaker->loan_application_id;
    
            if (isset($loanAppsMap[$loanAppId])) {
                $loanAppsMap[$loanAppId]['CoMaker'] = $loanCoMaker;
            }
        }
    
        // Convert the map to an array
        $loanAppsWithLoanFees = array_values($loanAppsMap);
    
        return [
            'data' => $loanAppsWithLoanFees,
        ];
    }    

    public function store(Request $request, PaymentScheduleServiceInterface $paymentScheduleService, LoanApplicationFeeController $loanApplicationFeeController, LoanApplicationCoMakerController $loanApplicationCoMakerController)
    {
        //return response()->json(['message' => 'hello'], Response::HTTP_INTERNAL_SERVER_ERROR);
        DB::beginTransaction();

        try {
            $userId = auth()->user()->id;
            $data = $request->input('allCustomerData');

            $checkPendingLoans = function() use ($data) {
                foreach ($data as $customerData) {

                    $document_status_code = Document_Status_Code::where('description', 'like', '%PENDING%')->first();

                    //get the pending loan
                    $pendingLoanExists = Loan_Application::where('customer_id', $customerData['customer_id'])
                    ->where('document_status_code', $document_status_code->id)
                    ->exists();

                    //return response()->json(['message create' => $debug],Response::HTTP_INTERNAL_SERVER_ERROR);

                    if ($pendingLoanExists) {
                        throw new \Exception('Cannot create a new loan application. There is already a pending loan for this customer.');
                    }
                }
            };

            $checkGroupBalances = function() use ($data) {
                $groupDatas = Customer::where('group_id', $data[0]['group_id'])->get();

                foreach ($groupDatas as $groupData) {
                    $totals = Payment_Schedule::where('customer_id', $groupData->id)
                        ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'PARTIALLY PAID, FORWARDED'])
                        ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance')
                        ->first();

                    $balance = $totals->balance ?? 0;

                    if ($balance > 0 && !is_null($totals)) {
                        throw new \Exception('There still member has not yet fully paid!');
                    }
                }
            };

            $checkCoMakerBalances = function() use ($data) {
                foreach ($data as $customerData) {
                    if (isset($customerData['coMaker']) && $customerData['coMaker'] > 0) {
                        $totals = Payment_Schedule::where('customer_id', $customerData['coMaker'])
                            ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'FORWARDED'])
                            ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance')
                            ->first();

                        $balance = $totals->balance ?? 0;

                        if ($balance > 0 && !is_null($totals)) {
                            throw new \Exception($customerData['customer_id'] . ' The coMaker has not yet fully paid!');
                        }
                    }
                }
            };

            // Move saveFeesAndPaymentSchedule function here so it's accessible to saveLoanApplication
            $saveFeesAndPaymentSchedule = function($customerData) use ($paymentScheduleService, $loanApplicationFeeController) {
                foreach ($customerData['fees'] as $feeId) {
                    $amount = Fees::where('id', $feeId)->first()->amount;
                    $loanId = Loan_Application::where('loan_application_no', $customerData['loan_application_no'])->first()->id;

                    $fees = [
                        'loan_application_id' => $loanId,
                        'fee_id' => $feeId,
                        'amount' => $amount,
                    ];
                    $payload = [
                        'customer_id' => $customerData['customer_id'],
                        'loan_released_id' => null,
                        'datetime_due' => now(),
                        'amount_due' => $amount,
                        'amount_interest' => 0,
                        'amount_paid' => 0,
                        'payment_status_code' => 'UNPAID',
                        'remarks' => 'FEES',
                    ];

                    $paymentScheduleService->createPaymentSchedule(new Request($payload));
                    $loanApplicationFeeController->store(new Request($fees));
                }
            };

            $saveLoanApplication = function($customerData) use ($userId, $saveFeesAndPaymentSchedule, $loanApplicationCoMakerController) {
                $customerData['document_status_code'] = Document_Status_Code::where('description', $customerData['document_status_code'])->first()->id;
                $customerData['datetime_prepared'] = now();
                $customerData['prepared_by_id'] = $userId;
                $customerData['last_modified_by_id'] = $userId;

                $payload = new Request($customerData);
                $this->loanApplicationService->createLoanApplication($payload);

                if (isset($customerData['coMaker']) && $customerData['coMaker'] > 0) {
                    $loanId = Loan_Application::where('loan_application_no', $customerData['loan_application_no'])->first()->id;
                    $coMaker = [
                        'loan_application_id' => $loanId,
                        'customer_id' => $customerData['customer_id'],
                    ];
                    $loanApplicationCoMakerController->store(new Request($coMaker));
                }

                $saveFeesAndPaymentSchedule($customerData);
            };

            // Run the validation and checks
            $checkPendingLoans();
            $checkGroupBalances();
            $checkCoMakerBalances();

            // Insert loan applications
            foreach ($data as $customerData) {
                $saveLoanApplication($customerData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Loan applications and fees successfully inserted',
                'data' => $data,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();

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

        // foreach($loanApp as $loan)
        // {
        //     if(!is_null($loanApp))
        //     {
        //         $loanApp = $loan;
        //     }
        // }

        //throw new \Exception(count($loanApp));

        return response()->json([
            'data' => $loanApp,
        ]);

    }

    public function seeWithPending(string $id, CustomerPersonalityController $customerPersonalityController, LoanApplicationFeeServiceInterface $loanApplicationFeeService, LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService)
    {
        //customer with approved and active
        $customer = $customerPersonalityController->indexApproveActivePending();


        //return response()->json(['message' => $customer], Response::HTTP_NOT_FOUND);

        $customerData = null;

        foreach($customer as $cus)
        {
            if(!is_null($cus))
            {
                foreach($cus as $c)
                {
                    // $d = $c['customer']->id;
                    if(!is_null($c))
                    {
                        if($c['customer']->id == $id)
                        {
                            $customerData = $c;
                            break;
                        }
                    }
                }
            }
        }

        if(is_null($customerData))
        {
            return response()->json(['message' => 'There is no customer found'], Response::HTTP_NOT_FOUND);
        }

        // Loan status id
        $loanDocumentId = Document_Status_Code::where('description', 'like', '%Pending%')->first()->id;

        // Find loan application by user ID and should be pending
        $loanApp = Loan_Application::where('customer_id', $customerData['customer']->id)
        ->where('document_status_code', $loanDocumentId)
        ->first();

        //get the loan application fees
        $loanFee = Loan_Application_Fees::where('loan_application_id', $loanApp->id)->get();

        $loanFeeData = [];
        foreach($loanFee as $fee)
        {
            if(!is_null($fee))
            {
                //get the loan fee description
                $loanFeeDescription = Fees::where('id', $fee->fee_id)->first()->description;
                $loanFee = [
                    'id' => $fee->id,  // Dynamically populated from the loan fee object
                    'loan_application_id' => $fee->loan_application_id,  // Dynamically populated from the loan fee object
                    'fee_id' => $fee->fee_id,  // Dynamically populated from the loan fee object
                    'amount' => $fee->amount,  // Dynamically populated from the loan fee object
                    'encoding_order' => $fee->encoding_order,  // Dynamically populated from the loan fee object
                    'created_at' => $fee->created_at,  // Dynamically populated from the loan fee object
                    'updated_at' => $fee->updated_at,  // Dynamically populated from the loan fee object
                    'description' => $loanFeeDescription,
                ];

                $loanFeeData[] = $loanFee;
            }
        }

        //return response()->json(['error' => $loanFeeData], 404);



        if(!$loanApp) {
            return response()->json(['error' => 'Loan Application not found'], 404);
        }
        // Convert the result to an array and then to an object to ensure it’s not wrapped in an array

        // return [
        //     $loanAppObject
        // ];

        $customerData['customer']['factor_rate_value'] = 12;

        //get the factor rate value
        $factor_rate_value = Factor_Rate::where('id', $loanApp->factor_rate)->first()->value;

        $payment_frequency_description = Payment_Frequency::where('id', $loanApp->payment_frequency_id)->first()->description;
        $payment_duration_description = Payment_Duration::where('id', $loanApp->payment_duration_id)->first()->description;

        $loanApp = [
            'id' => $loanApp->id,  // Dynamically populated from the loan application
            'customer_id' => $loanApp->customer_id,  // Dynamically populated from the loan application
            'group_id' => $loanApp->group_id,  // Dynamically populated from the loan application
            'datetime_prepared' => $loanApp->datetime_prepared,  // Dynamically populated from the loan application
            'document_status_code' => $loanApp->document_status_code,  // Dynamically populated from the loan application
            'loan_application_no' => $loanApp->loan_application_no,  // Dynamically populated from the loan application
            'amount_loan' => $loanApp->amount_loan,  // Dynamically populated from the loan application
            'factor_rate' => $loanApp->factor_rate,  // Dynamically populated from the loan application
            'amount_interest' => $loanApp->amount_interest,  // Dynamically populated from the loan application
            'amount_paid' => $loanApp->amount_paid,  // Dynamically populated from the loan application
            'datetime_target_release' => $loanApp->datetime_target_release,  // Dynamically populated from the loan application
            'datetime_fully_paid' => $loanApp->datetime_fully_paid,  // Dynamically populated from the loan application
            'datetime_approved' => $loanApp->datetime_approved,  // Dynamically populated from the loan application
            'payment_frequency_id' => $loanApp->payment_frequency_id,  // Dynamically populated from the loan application
            'payment_duration_id' => $loanApp->payment_duration_id,  // Dynamically populated from the loan application
            'approved_by_id' => $loanApp->approved_by_id,  // Dynamically populated from the loan application
            'prepared_by_id' => $loanApp->prepared_by_id,  // Dynamically populated from the loan application
            'released_by_id' => $loanApp->released_by_id,  // Dynamically populated from the loan application
            'last_modified_by_id' => $loanApp->last_modified_by_id,  // Dynamically populated from the loan application
            'notes' => $loanApp->notes,  // Dynamically populated from the loan application
            'created_at' => $loanApp->created_at,  // Dynamically populated from the loan application
            'updated_at' => $loanApp->updated_at,  // Dynamically populated from the loan application
            'factor_rate_value' => $factor_rate_value,
            'payment_frequency_description' =>  $payment_frequency_description,
            'payment_duration_description' => $payment_duration_description,
            'fees' => $loanFeeData,
        ];

        $loanApp = [
            'customer' => $customerData['customer'],
            'personality' => $customerData['personality'],
            'loan' => $loanApp,
        ];

        // Convert to an object if necessary
        $loanAppObject = (object) $loanApp;

        return response()->json([
            'data' => $loanAppObject,
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

    public function approve(Request $request, CustomerPersonalityController $customerPersonalityController, int $id, LoanApplicationServiceInterface $loanApplicationService, LoanReleaseServiceInterface $loanReleaseService, PaymentScheduleServiceInterface $paymentScheduleService)
{
    // Start the transaction
    DB::beginTransaction();

    try {

        $userId = auth()->user()->id;
        $customerId = $request['customer_id'];
        $loanId = $request['id'];

        $AllCustomerData = $request->input('allCustomerData');

        //check if the group mates has its own dues
        $customer_data = $customerPersonalityController->index();

        $customer_group_id = null;

        foreach($AllCustomerData as $data)
        {
            if(!is_null($data))
            {
                $customer_group_id = $data['group_id'];
                break;
            }
        }

        // Define helper functions within store
        if (count($data) < 4) {
            throw new \Exception('The amount of customers should be at least 4');
        };

        $payableFee = null;
        foreach($customer_data as $id => $data)
        {

            if(!is_null($data))
            {
                foreach($data as $dat)
                {
                    if(!is_null($dat))
                    {

                        if($dat['customer']['group_id'] == $customer_group_id)
                        {
                            $payableFee = Payment_Schedule::where('customer_id', $dat['customer']['id'])
                            ->where('payment_status_code', 'like', '%UNPAID%')
                            ->orWhere('payment_status_code', 'like', '%PARTIALLY PAID%')
                            ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED'])
                            ->selectRaw('SUM(amount_due) - SUM(amount_paid) as fee_balance')
                            ->first();

                            if ($payableFee && $payableFee->fee_balance > 0) {
                                throw new \Exception('Cannot approve: outstanding fees need to be paid');
                            }

                            if(!($dat['personality']['personality_status_code'] == Personality_Status_Map::where('description', 'like', '%APPROVED%')->first()->id)
                            && !($dat['personality']['credit_status_id'] == Credit_Status::where('description', 'like', '%ACTIVE%')->first()->id))
                            {
                                throw new \Exception('A member in the group is not APPROVED nor ACTIVE');
                            }
                        }
                    }
                }
            }
        }

        //return response()->json(['message' => 'done no error'], Response::HTTP_INTERNAL_SERVER_ERROR);

        foreach($AllCustomerData as $data)
        {
            if(!is_null($data))
            {
                //return response()->json(['message' => $data], Response::HTTP_INTERNAL_SERVER_ERROR);
                $loanApplicationNo = $data['loan_application_no'];
                $customerId = $data['customer_id'];
                //search loang application id
                $loanId = Loan_Application::where('loan_application_no', $loanApplicationNo)->first()->id;
            $loanApproveId = Document_Status_Code::where('description', 'Approved')->first()->id;

            $loanApplication = Loan_Application::findOrFail($loanId);

            $loanApplication->document_status_code = $loanApproveId;
            $loanApplication->approved_by_id = $userId;
            $loanApplication->released_by_id = $userId;
            $loanApplication->datetime_approved = now();
            $loanApplication->save();
            $loanApplication->fresh();

            $passbookNo = Customer::where('id', $customerId)->first()->passbook_no;

            $loanAmount = $data['amount_loan'];
            $factorRateId = $data['factor_rate'];
            $amountInterest = $data['amount_interest'];

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

            //create loan release data
            $loanRelease = $loanReleaseService->createLoanRelease($loanReleasePayload);

            //get the numuber of payments
            $numberOfPayments = $paymentDuration->number_of_payments;

            //calculate amount due
            $amountDue = (($loanAmount + $amountInterest) / $numberOfPayments);

            //get amount interest
            $amountInterestPerPayment = $amountInterest / $numberOfPayments;

            $firstDueDate = $loanReleasePayload['datetime_first_due'];

            //throw new \Exception($amountDue);

            $paymentFrequency = $paymentFrequency->days_interval; // Weekly interval in days

            $holidays = Holiday::get();

            for($i = 0; $i < $numberOfPayments; $i++)
            {
                foreach($holidays as $k => $holiday)
                {
                    if(!is_null($holiday))
                    {
                        if($firstDueDate == $holiday->date || $firstDueDate->isSunday())
                        {
                            $debug[$i] = $firstDueDate = $firstDueDate->addDays();
                        }
                    }
                }

                $payload = [
                    'customer_id' => $customerId,
                    'loan_released_id' => $loanRelease->id,
                    'datetime_due' => $firstDueDate,
                    'amount_due' => $amountDue,
                    'amount_interest' => $amountInterestPerPayment,
                    'amount_paid' => 0,
                    'payment_status_code' => 'UNPAID',
                    'remarks' => null,
                ];

                $payload = new Request($payload);
                $paymentScheduleService->createPaymentSchedule($payload);

                $dateDebug[$i] = $firstDueDate = $firstDueDate->copy()->addDays($paymentFrequency);
                }
            }


        }


        //return response()->json(['message' => $debug, 'message date' => $dateDebug], Response::HTTP_INTERNAL_SERVER_ERROR);

        DB::commit();

        return response()->json(['message' => 'Loan release and payment schedule created successfully.'], Response::HTTP_OK);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function reject(Request $request, CustomerPersonalityController $customerPersonalityController, int $id, LoanApplicationServiceInterface $loanApplicationService, LoanReleaseServiceInterface $loanReleaseService, PaymentScheduleServiceInterface $paymentScheduleService)
{
    // Start the transaction
    DB::beginTransaction();

    try {

        $userId = auth()->user()->id;

        $customerId = $request['customer_id'];

        $loanId = $request['id'];

        $AllCustomerData = $request->input('allCustomerData');

        //check if the group mates has its own dues
        $customer_data = $customerPersonalityController->index();

        $customer_group_id = null;

        foreach($AllCustomerData as $data)
        {
            if(!is_null($data))
            {
                $customer_group_id = $data['group_id'];
                break;
            }
        }

        $payableFee = null;
        foreach($customer_data as $id => $data)
        {
            if(!is_null($data))
            {
                foreach($data as $dat)
                {
                    if(!is_null($dat))
                    {

                        if($dat['customer']['group_id'] == $customer_group_id)
                        {
                            $payableFee = Payment_Schedule::where('customer_id', $dat['customer']['id'])
                            ->where('payment_status_code', 'like', '%UNPAID%')
                            ->orWhere('payment_status_code', 'like', '%PARTIALLY PAID%')
                            ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED'])
                            ->selectRaw('SUM(amount_due) - SUM(amount_paid) as fee_balance')
                            ->first();

                            if ($payableFee && $payableFee->fee_balance > 0) {
                                throw new \Exception('Cannot approve: outstanding fees need to be paid');
                            }

                            if(!($dat['personality']['personality_status_code'] == Personality_Status_Map::where('description', 'like', '%APPROVED%')->first()->id)
                            && !($dat['personality']['credit_status_id'] == Credit_Status::where('description', 'like', '%ACTIVE%')->first()->id))
                            {
                                throw new \Exception('A member in the group is not APPROVED nor ACTIVE');
                            }

                            $document_status_reject = Document_Status_Code::where('id', $dat['document_status_code'])
                            ->first();

                            if(isset($document_status_reject) && !is_null($document_status_reject))
                            {
                                if($document_status_reject->id == $dat['document_status_code'])
                                {
                                    return response()->json(['message' => "The customer {$dat['personality']['family_name']} {$dat['personality']['first_name']} {$dat['personality']['middle_name']}"], Response::HTTP_INTERNAL_SERVER_ERROR);
                                }
                            }
                        }
                    }
                }
            }
        }

        //return response()->json(['message' => 'done no error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        foreach($AllCustomerData as $data)
        {
            if(!is_null($data))
            {
                //return response()->json(['message' => $data], Response::HTTP_INTERNAL_SERVER_ERROR);
                $loanApplicationNo = $data['loan_application_no'];
                $customerId = $data['customer_id'];

                //search loang application id
                $loanId = Loan_Application::where('loan_application_no', $loanApplicationNo)->first()->id;
                $loanApproveId = Document_Status_Code::where('description', 'Reject')->first()->id;

                $loanApplication = Loan_Application::findOrFail($loanId);

                $loanApplication->document_status_code = $loanApproveId;
                $loanApplication->rejected_by_id = $userId;
                $loanApplication->last_modified_by_id = $userId;
                $loanApplication->datetime_rejected = now();
                $loanApplication->save();
                $loanApplication->fresh();
            }
        }

        //return response()->json(['message' => $debug, 'message date' => $dateDebug], Response::HTTP_INTERNAL_SERVER_ERROR);

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
