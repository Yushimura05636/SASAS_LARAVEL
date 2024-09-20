<?php

namespace App\Repository;
use App\Interface\Repository\LoanCountRepositoryInterface;
use App\Models\Loan_Count;
use Illuminate\Http\Response;

class LoanCountRepository implements LoanCountRepositoryInterface
{
    public function findMany()
    {
        return Loan_Count::paginate(10);
    }

    public function findOneById($id)
    {
        return Loan_Count::findOrFail($id);
    }

    public function create(object $payload)
    {
        $loanCount = new Loan_Count();

        $loanCount->loan_count = $payload->loan_count;
        $loanCount->min_amount = $payload->min_amount;
        $loanCount->max_amount = $payload->max_amount;
        $loanCount->save();

        return $loanCount->fresh();
    }

    public function update(object $payload, int $id)
    {
        $loanCount = Loan_Count::findOrFail($id);

        $loanCount->loan_count = $payload->loan_count;
        $loanCount->min_amount = $payload->min_amount;
        $loanCount->max_amount = $payload->max_amount;
        $loanCount->save();

        return $loanCount->fresh();
    }

    public function delete(int $id)
    {
        $loanCount = Loan_Count::findOrFail($id);
        $loanCount->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
