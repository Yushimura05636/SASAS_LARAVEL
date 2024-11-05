<?php

namespace App\Repository;
use App\Interface\Repository\LoanApplicationCoMakerRepositoryInterface;
use App\Models\Loan_Application_Comaker;
use Illuminate\Http\Response;

class LoanApplicationCoMakerRepository implements LoanApplicationCoMakerRepositoryInterface
{
    public function findMany()
    {
        return Loan_Application_Comaker::paginate(10);
    }

    public function findOneById($id)
    {
        return Loan_Application_Comaker::findOrFail($id);
    }

    public function create(object $payload)
    {
        $loanApplicationCoMaker = new Loan_Application_Comaker();
        $loanApplicationCoMaker->loan_application_id = $payload->loan_application_id;
        $loanApplicationCoMaker->customer_id = $payload->customer_id;
        $loanApplicationCoMaker->encoding_order = $payload->encoding_order;
        $loanApplicationCoMaker->save();

        return $loanApplicationCoMaker->fresh();
    }

    public function update(object $payload, int $id)
    {
        $loanApplicationCoMaker = Loan_Application_Comaker::findOrFail($id);
        $loanApplicationCoMaker->loan_application_id = $payload->loan_application_id;
        $loanApplicationCoMaker->customer_id = $payload->customer_id;
        $loanApplicationCoMaker->encoding_order = $payload->encoding_order;
        $loanApplicationCoMaker->save();

        return $loanApplicationCoMaker->fresh();
    }

    public function delete(int $id)
    {
        $loanApplicationCoMaker = Loan_Application_Comaker::findOrFail($id);
        $loanApplicationCoMaker->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
