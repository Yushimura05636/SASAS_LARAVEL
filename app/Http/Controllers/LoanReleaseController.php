<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanReleaseStoreRequest;
use App\Http\Requests\LoanReleaseUpdateRequest;
use App\Interface\Service\LoanReleaseServiceInterface;
use App\Models\Loan_Application;
use App\Models\Loan_Release;
use App\Models\User_Account;
use Illuminate\Http\Request;

class LoanReleaseController extends Controller
{
    private $loanReleaseService;

    public function __construct(LoanReleaseServiceInterface $loanReleaseService)
    {
        $this->loanReleaseService = $loanReleaseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loan_releases = $this->loanReleaseService->findLoanRelease(); // Assuming it returns a collection

        if ($loan_releases->isNotEmpty()) {
            // Map each loan release into a custom structure
            $customData = $loan_releases->map(function ($loan_release) {
                // Get related loan application and prepared_by user
                $loan_application = Loan_Application::where('id', $loan_release->loan_application_id)->first(); // Adjust the foreign key
                $prepared_by = User_Account::where('id', $loan_release->prepared_by_id)->first(); // Adjust the foreign key

                return [
                    'datetime_prepared' => $loan_release->datetime_prepared,
                    'passbook_number' => $loan_release->passbook_number,
                    'loan_application_no' => $loan_application->loan_application_no ?? null,
                    'full_name' => $prepared_by->last_name . ' ' . $prepared_by->first_name . ' ' . $prepared_by->middle_name,
                    'notes' => $loan_release->notes,
                    'datetime_first_due' => $loan_release->datetime_first_due,
                ];
            });

            return $customData; // This will return a collection of custom arrays
        }
            // Return an empty array if no records found
            return [];


        //return $loan_release; //return if null the first parse
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LoanReleaseStoreRequest $request)
    {
        return $this->loanReleaseService->createLoanRelease($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->loanReleaseService->findLoanReleaseById($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LoanReleaseUpdateRequest $request, int $id)
    {
        return $this->loanReleaseService->updateLoanRelease($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->loanReleaseService->deleteLoanRelease($id);
    }

    public function generateSchedule(Request $request)
    {

    }
}
