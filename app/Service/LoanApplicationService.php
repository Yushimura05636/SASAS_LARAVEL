<?php

namespace App\Service;

use App\Http\Resources\LoanApplicationResource;
use App\Interface\Service\LoanApplicationServiceInterface;
use App\Repository\LoanApplicationRepository;

class LoanApplicationService implements LoanApplicationServiceInterface
{
    private $loanApplicationRepository;
    public function __construct(LoanApplicationRepository $loanApplicationRepository)
    {
        $this->loanApplicationRepository = $loanApplicationRepository;
    }

    public function findLoanApplication()
    {
        $loanApplicationRepository =$this->loanApplicationRepository->findMany();
        return LoanApplicationResource::collection($loanApplicationRepository);
    }

    public function findLoanApplicationById(int $id)
    {
        $loanApplicationRepository =$this->loanApplicationRepository->findOneById($id);
        return new LoanApplicationResource($loanApplicationRepository);
    }

    public function createLoanApplication(object $payload)
    {
        $loanApplicationRepository =$this->loanApplicationRepository->create($payload);
        return new LoanApplicationResource($loanApplicationRepository);
    }

    public function updateLoanApplication(object $payload, int $id)
    {
        $loanApplicationRepository =$this->loanApplicationRepository->update($payload, $id);
        return new LoanApplicationResource($loanApplicationRepository);
    }

    public function deleteLoanApplication(int $id)
    {
        return $this->loanApplicationRepository->delete($id);
    }
}

