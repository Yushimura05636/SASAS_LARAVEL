<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanReleaseStoreRequest;
use App\Http\Requests\LoanReleaseUpdateRequest;
use App\Interface\Service\LoanReleaseServiceInterface;
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
        return $this->loanReleaseService->findLoanRelease();
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
}
