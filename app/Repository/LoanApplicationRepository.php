<?php
namespace App\Repository;

use App\Interface\Repository\LoanApplicationRepositoryInterface;
use App\Models\Loan_Application;
use Illuminate\Http\Response;

class LoanApplicationRepository implements LoanApplicationRepositoryInterface
{

    public function findMany()
    {
        return Loan_Application::all();
    }

    public function findOneById(int $id)
    {
        return Loan_Application::findOrFail($id);
    }

    public function create(object $payload)
    {
        $loanapplication = new Loan_Application();

        $loanapplication->customer_id = $payload->customer_id; //
        $loanapplication->datetime_prepared = $payload->datetime_prepared;
        $loanapplication->group_id = $payload->group_id; //
        $loanapplication->document_status_code = $payload->document_status_code; //
        $loanapplication->loan_application_no = $payload->loan_application_no; //
        $loanapplication->amount_loan = $payload->amount_loan;
        $loanapplication->factor_rate = $payload->factor_rate;
        $loanapplication->amount_interest = $payload->amount_interest;
        $loanapplication->amount_paid = $payload->amount_paid;
        $loanapplication->datetime_target_release = $payload->datetime_target_release;
        $loanapplication->datetime_fully_paid = $payload->datetime_fully_paid;
        $loanapplication->datetime_approved = $payload->datetime_approved;
        $loanapplication->payment_frequency_id = $payload->payment_frequency_id;
        $loanapplication->payment_duration_id = $payload->payment_duration_id;
        $loanapplication->approved_by_id = $payload->approved_by_id;
        $loanapplication->prepared_by_id = $payload->prepared_by_id;
        $loanapplication->released_by_id = $payload->released_by_id;
        $loanapplication->last_modified_by_id = $payload->last_modified_by_id;
        $loanapplication->notes = $payload->notes;

        $loanapplication->save();
        return $loanapplication->fresh();

    }

    public function update(object $payload, int $id)
    {
        $loanapplication = Loan_Application::findOrFail($id);

        $loanapplication->customer_id = $payload->customer_id;
        $loanapplication->datetime_prepared = $payload->datetime_prepared;
        $loanapplication->group_id = $payload->group_id;
        $loanapplication->document_status_code = $payload->document_status_code;
        $loanapplication->loan_application_no = $payload->loan_application_no;
        $loanapplication->amount_loan = $payload->amount_loan;
        $loanapplication->factor_rate = $payload->factor_rate;
        $loanapplication->amount_interest = $payload->amount_interest;
        $loanapplication->amount_paid = $payload->amount_paid;
        $loanapplication->datetime_target_release = $payload->datetime_target_release;
        $loanapplication->datetime_fully_paid = $payload->datetime_fully_paid;
        $loanapplication->datetime_approved = $payload->datetime_approved;
        $loanapplication->payment_frequency_id = $payload->payment_frequency_id;
        $loanapplication->payment_duration_id = $payload->payment_duration_id;
        $loanapplication->approved_by_id = $payload->approved_by_id;
        $loanapplication->prepared_by_id = $payload->prepared_by_id;
        $loanapplication->released_by_id = $payload->released_by_id;
        $loanapplication->last_modified_by_id = $payload->last_modified_by_id;
        $loanapplication->notes = $payload->notes;

        $loanapplication->save();
        return $loanapplication->fresh();
    }

    public function delete(int $id)
    {
        $loanapplication = Loan_Application::findOrFail($id);
        $loanapplication->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
