<?php

namespace App\Service;

use App\Http\Resources\LoanCountResource;
use App\Interface\Repository\LoanCountRepositoryInterface;
use App\Interface\Service\LoanCountServiceInterface;
use App\Models\Loan_Count;

class LoanCountService implements LoanCountServiceInterface
{
    private $loanCountRepository;

    public function __construct(LoanCountRepositoryInterface $loanCountRepository)
    {
        $this->loanCountRepository = $loanCountRepository;
    }

    public function findLoanCounts()
    {
        $loanCountRepository = $this->loanCountRepository->findMany();

        return LoanCountResource::collection($loanCountRepository);
    }

    public function findLoanCountById(int $id)
    {
        $loanCountRepository = $this->loanCountRepository->findOneById($id);

        return new LoanCountResource($loanCountRepository);
    }

    public function createLoanCount(object $payload)
    {
        $loanCountRepository = $this->loanCountRepository->create($payload);

        return new LoanCountResource($loanCountRepository);
    }

    public function updateLoanCount(object $payload, int $id)
    {
        $loanCountRepository = $this->loanCountRepository->update($payload, $id);

        return new LoanCountResource($loanCountRepository);
    }

    public function deleteLoanCount(int $id)
    {
        return $this->loanCountRepository->delete($id);
    }
}
