<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanCountStoreRequest;
use App\Http\Requests\LoanCountUpdateRequest;
use App\Interface\Service\LoanCountServiceInterface;

class LoanCountController extends Controller
{
    private $loanCountService;
    public function __construct(LoanCountServiceInterface $loanCountService)
    {
        $this->loanCountService = $loanCountService;
    }

    public function index()
    {
        return $this->loanCountService->findLoanCounts();
    }

    public function show(int $id)
    {
        return $this->loanCountService->findLoanCountById($id);
    }

    public function store(LoanCountStoreRequest $request)
    {
        return $this->loanCountService->createLoanCount($request);
    }

    public function update(LoanCountUpdateRequest $request, int $id)
    {
        return $this->loanCountService->updateLoanCount($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->loanCountService->deleteLoanCount($id);
    }
}
