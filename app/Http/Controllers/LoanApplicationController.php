<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationStoreRequest;
use App\Http\Requests\LoanApplicationUpdateRequest;
use App\Interface\Service\FeeServiceInterface;
use App\Interface\Service\LoanApplicationCoMakerServiceInterface;
use App\Interface\Service\LoanApplicationFeeServiceInterface;
use App\Interface\Service\LoanApplicationServiceInterface;
use App\Models\Customer;
use App\Models\Document_Status_code;
use App\Models\Factor_Rate;
use App\Models\Fees;
use App\Models\Loan_Application;
use App\Models\Loan_Application_Comaker;
use App\Models\Loan_Application_Fees;
use App\Models\Loan_Count;
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

            $data[$i]['factor_rate'] = Factor_Rate::where('id', $data[$i]['factor_rate'])->first()->value;

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
}
