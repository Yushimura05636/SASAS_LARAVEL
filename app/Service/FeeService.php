<?php

namespace App\Service;

use App\Http\Resources\FeeResource;
use App\Interface\Repository\FeeRepositoryInterface;
use App\Interface\Service\FeeServiceInterface;

class FeeService implements FeeServiceInterface
{
    private $feeRepository;

    public function __construct(FeeRepositoryInterface $feeRepository)
    {
        $this->feeRepository = $feeRepository;
    }

    public function findFees()
    {
        $fee = $this->feeRepository->findMany();

        return FeeResource::collection($fee);
    }

    public function findFeeById(int $id)
    {
        $fee = $this->feeRepository->findOneById($id);

        return new FeeResource($fee);
    }

    public function createFee(object $payload)
    {
        $fee = $this->feeRepository->create($payload);

        return new FeeResource($fee);
    }

    public function updateFee(object $payload, int $id)
    {
        $fee = $this->feeRepository->update($payload, $id);

        return new FeeResource($fee);
    }

    public function deleteFee(int $id)
    {
        $fee = $this->feeRepository->delete($id);

        return new FeeResource($fee);
    }
}
