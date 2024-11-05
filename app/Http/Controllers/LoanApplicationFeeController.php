<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationFeeStoreRequest;
use App\Http\Requests\LoanApplicationFeeUpdateRequest;
use App\Interface\Service\LoanApplicationFeeServiceInterface;
use Illuminate\Http\Request;

class LoanApplicationFeeController extends Controller
{
    private $loanApplicationFeeService;

    public function __construct(LoanApplicationFeeServiceInterface $loanApplicationFeeService)
    {
        $this->loanApplicationFeeService = $loanApplicationFeeService;
    }

    public function index()
    {
        $this->loanApplicationFeeService->findLoanFees();
    }

    public function store(Request $request)
    {
        return $this->loanApplicationFeeService->createLoanFee($request);
    }

    public function show(int $id)
    {
        return $this->loanApplicationFeeService->findLoanFeeById($id);
    }

    public function update(LoanApplicationFeeUpdateRequest $request, int $id)
    {
        return $this->loanApplicationFeeService->updateLoanFee($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->loanApplicationFeeService->deleteLoanFee($id);
    }
}
