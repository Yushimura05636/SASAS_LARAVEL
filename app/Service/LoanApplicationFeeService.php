<?php

namespace App\Service;

use App\Http\Resources\LoanApplicationFeeResource;
use App\Interface\Repository\LoanApplicationFeeRepositoryInterface;
use App\Interface\Service\LoanApplicationFeeServiceInterface;

class LoanApplicationFeeService implements LoanApplicationFeeServiceInterface
{
    private $loanApplicaitonFeeRepositroy;

    public function __construct(LoanApplicationFeeRepositoryInterface $loanApplicaitonFeeRepositroy)
    {
        $this->loanApplicaitonFeeRepositroy = $loanApplicaitonFeeRepositroy;
    }

    public function findLoanFees()
    {
        $loanApplicationFee = $this->loanApplicaitonFeeRepositroy->findMany();

        return LoanApplicationFeeResource::collection($loanApplicationFee);
    }

    public function findLoanFeeById($id)
    {
        $loanApplicaitonFee = $this->loanApplicaitonFeeRepositroy->findOneById($id);

        return new LoanApplicationFeeResource($loanApplicaitonFee);
    }

    public function createLoanFee(object $payload)
    {
        $loanApplicationFee = $this->loanApplicaitonFeeRepositroy->create($payload);

        return new LoanApplicationFeeResource($loanApplicationFee);
    }

    public function updateLoanFee(object $payload, int $id)
    {
        $loanApplicationFee = $this->loanApplicaitonFeeRepositroy->update($payload, $id);

        return new LoanApplicationFeeResource($loanApplicationFee);
    }

    public function deleteLoanFee(int $id)
    {
        return $this->loanApplicaitonFeeRepositroy->delete($id);
    }
}
