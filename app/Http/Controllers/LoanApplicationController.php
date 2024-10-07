<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationStoreRequest;
use App\Http\Requests\LoanApplicationUpdateRequest;
use App\Interface\Service\LoanApplicationCoMakerServiceInterface;
use App\Interface\Service\LoanApplicationFeeServiceInterface;
use App\Interface\Service\LoanApplicationServiceInterface;
use App\Models\Customer;
use App\Models\Document_Status_code;
use App\Models\Factor_Rate;
use App\Models\Fees;
use App\Models\Loan_Application;
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
    public function index()
    {
        return $this->loanApplicationService->findLoanApplication();
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
    public function show(string $id)
    {
        return $this->loanApplicationService->findLoanApplicationById($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LoanApplicationUpdateRequest $request, int $id)
    {
        return $this->loanApplicationService->updateLoanApplication($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->loanApplicationService->deleteLoanApplication( $id);
    }
}
