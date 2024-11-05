<?php

namespace App\Service;

use App\Http\Resources\CustomerRequirementResource;
use App\Http\Resources\CustomerRequirementsResource;
use App\Interface\Repository\CustomerRequirementRepositoryInterface;
use App\Interface\Service\CustomerRequirementServiceInterface;

class CustomerRequirementService implements CustomerRequirementServiceInterface
{
    private $customerRequirementRepository;

    public function __construct(CustomerRequirementRepositoryInterface $customerRequirementRepository)
    {
        $this->customerRequirementRepository = $customerRequirementRepository;
    }

    public function findCustomerRequirements()
    {
        $customerRequirements = $this->customerRequirementRepository->findMany();

        return CustomerRequirementResource::collection($customerRequirements);
    }

    public function findCustomerRequirementById(int $id)
    {
        $customerRequirements = $this->customerRequirementRepository->findOneById($id);

        return new CustomerRequirementResource($customerRequirements);

    }

    public function createCustomerRequirement(object $payload)
    {
        $customerRequirements = $this->customerRequirementRepository->create($payload);

        return new CustomerRequirementResource($customerRequirements);

    }

    public function updateCustomerRequirement(object $payload, int $id)
    {
        $customerRequirements = $this->customerRequirementRepository->update($payload, $id);

        return new CustomerRequirementResource($customerRequirements);
    }

    public function deleteCustomerRequirement(int $id)
    {
        return $this->customerRequirementRepository->delete($id);
    }
}
