<?php

namespace App\Repository;

use App\Interface\Repository\LoanReleaseRepositoryInterface;
use App\Models\Loan_Release;
use Illuminate\Http\Response;

class LoanReleaseRepositority implements LoanReleaseRepositoryInterface
{
    public function findMany()
    {
        return Loan_Release::paginate(10);
    }

    public function findOneById($id)
    {
        return Loan_Release::find($id);
    }

    public function create(object $payload)
    {
        $loanRelease = new Loan_Release();
        $loanRelease->datetime_prepared = $payload->datetime_prepared;
        $loanRelease->passbook_number = $payload->passbook_number;
        $loanRelease->loan_application_id = $payload->loan_application_id;
        $loanRelease->prepared_by_id = $payload->prepared_by_id;
        $loanRelease->datetime_first_due = $payload->datetime_first_due;
        $loanRelease->notes = $payload->notes;
        $loanRelease->save();
        return $loanRelease->fresh();
    }

    public function update(object $payload, int $id)
    {
        $loanRelease = Loan_Release::findOrFail($id);
        $loanRelease->datetime_created = $payload->datetime_created;
        $loanRelease->datetime_prepared = $payload->datetime_prepared;
        $loanRelease->passbook_number = $payload->passbook_number;
        $loanRelease->loan_application_id = $payload->loan_application_id;
        $loanRelease->prepared_by_id = $payload->prepared_by_id;
        $loanRelease->datetime_first_due = $payload->datetime_first_due;
        $loanRelease->notes = $payload->notes;
        $loanRelease->save();
        return $loanRelease->fresh();
    }

    public function delete(int $id)
    {
        $loanRelease = Loan_Release::findOrFail($id);
        $loanRelease->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
