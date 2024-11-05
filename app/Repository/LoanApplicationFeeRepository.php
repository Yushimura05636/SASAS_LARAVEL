<?php

namespace App\Repository;
use App\Interface\Repository\LoanApplicationFeeRepositoryInterface;
use App\Models\Loan_Application_Fees;
use Illuminate\Http\Response;

class LoanApplicationFeeRepository implements LoanApplicationFeeRepositoryInterface
{
    public function findMany()
    {
        return Loan_Application_Fees::paginate(10);
    }

    public function findOneById(int $id)
    {
        return Loan_Application_Fees::findOrFail($id);
    }

    public function create(object $payload)
    {
        $loanApplicationFee = new Loan_Application_Fees();
        $loanApplicationFee->loan_application_id = $payload->loan_application_id;
        $loanApplicationFee->fee_id = $payload->fee_id;
        $loanApplicationFee->amount = $payload->amount;
        $loanApplicationFee->encoding_order = $payload->encoding_order;
        $loanApplicationFee->save();

        return $loanApplicationFee->fresh();
    }

    public function update(object $payload, int $id)
    {
        $loanApplicationFee = Loan_Application_Fees::findOrFail($id);
        $loanApplicationFee->loan_application_id = $payload->loan_application_id;
        $loanApplicationFee->fee_id = $payload->fee_id;
        $loanApplicationFee->amount = $payload->amount;
        $loanApplicationFee->encoding_order = $payload->encoding_order;
        $loanApplicationFee->save();

        return $loanApplicationFee->fresh();
    }

    public function delete(int $id)
    {
        $loanApplicaitonFee = Loan_Application_Fees::findOrFail($id);
        $loanApplicaitonFee->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
