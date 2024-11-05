<?php

namespace App\Service;

use App\Http\Resources\LoanReleaseResource;
use App\Interface\Repository\LoanReleaseRepositoryInterface;
use App\Interface\Service\LoanReleaseServiceInterface;

class LoanReleaseService implements LoanReleaseServiceInterface
{
    private $loanReleaseRepository;
    public function __construct(LoanReleaseRepositoryInterface $loanReleaseRepository)
    {
        $this-> loanReleaseRepository = $loanReleaseRepository;
    }

    public function findLoanRelease()
    {
        $loanReleaseRepository = $this->loanReleaseRepository->findMany();

        return LoanReleaseResource::collection($loanReleaseRepository);
    }

    public function findLoanReleaseById(int $id)
    {
        $loanReleaseRepository = $this->loanReleaseRepository->findOneById($id);
        return new LoanReleaseResource($loanReleaseRepository);
    }

    public function createLoanRelease(object $payload)
    {
        $loanReleaseRepository = $this->loanReleaseRepository->create($payload);
        return new LoanReleaseResource($loanReleaseRepository);

    }

    public function updateLoanRelease(object $payload, int $id)
    {
        $loanReleaseRepository = $this->loanReleaseRepository->update($payload, $id);
        return new LoanReleaseResource($loanReleaseRepository);

    }

    public function deleteLoanRelease(int $id)
    {
        return $this->loanReleaseRepository->delete($id);
    }
}
