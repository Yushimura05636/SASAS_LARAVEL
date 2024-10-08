<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationCoMakerStoreRequest;
use App\Http\Requests\LoanApplicationCoMakerUpdateRequest;
use App\Http\Resources\LoanApplicationCoMakerResource;
use App\Interface\Service\LoanApplicationCoMakerServiceInterface;
use App\Models\Loan_Application_Comaker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanApplicationCoMakerController extends Controller
{
    private $loanApplicationCoMakerService;

    public function __construct(LoanApplicationCoMakerServiceInterface $loanApplicationCoMakerService)
    {
        $this->loanApplicationCoMakerService = $loanApplicationCoMakerService;
    }

    public function index()
    {
        return $this->loanApplicationCoMakerService->findCoMakers();
    }

    public function store(Request $request)
    {
        return $this->loanApplicationCoMakerService->createLoanCoMaker($request);
    }

    public function show(int $id)
    {
        return $this->loanApplicationCoMakerService->findLoanCoMakerById($id);
    }

    public function look(int $id)
    {
        return new JsonResource(Loan_Application_Comaker::where('loan_application_id', $id)->first());
    }

    public function update(LoanApplicationCoMakerUpdateRequest $request, int $id)
    {
        return $this->loanApplicationCoMakerService->updateLoanCoMaker($request, $id);
    }
}
