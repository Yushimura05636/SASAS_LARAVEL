<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationStoreRequest;
use App\Http\Requests\LoanApplicationUpdateRequest;
use App\Interface\Service\LoanApplicationServiceInterface;
use App\Models\Document_Status_code;
use App\Models\Factor_Rate;
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

public function store(Request $request)
{
    $userId = auth()->user()->id;
    $data = $request->input('allCustomerData');  // Assuming 'allCustomerData' is an array

    // Start a database transaction
    DB::beginTransaction();

    try {
        for ($i = 0; $i < count($data); $i++) {
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

            // Insert the loan application
            $this->loanApplicationService->createLoanApplication($payload);

            // Insert the fees here (you can implement this part if needed)

            // return response()->json([
            //     'message' => 'An error occurred while processing the transaction',
            //     'error' => 'error',
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
