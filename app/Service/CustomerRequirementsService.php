<?php

namespace App\Service;

use App\Http\Resources\CustomerRequirementsResource;
use App\Interface\Requirements\CustomerRequirementsRepositoryInterface;
use App\Interface\Service\CustomerRequirementServiceInterface;

class CustomerRequirementService implements CustomerRequirementServiceInterface 
{
    private $customer_requirementsRepository;

    public function __construct(CustomerRequirementsRepositoryInterface $customer_requirementsRepository)
    {
        $this->customer_requirementsRepository = $customer_requirementsRepository;
    }

    public function findCustomerRequirements()
    {
        $customer_requirements = $this->customer_requirementsRepository->findMany();

        return CustomerRequirementsResource::collection($customer_requirements);
    }

    public function findCustomerRequirementById(int $id)
    {
        $customer_requirements = $this->customer_requirementsRepository->findOneById($id);

        return new CustomerRequirementsResource($customer_requirements);

    }

    public function createCustomerRequirement(object $payload)
    {
        $customer_requirements = $this->customer_requirementsRepository->create($payload);

        return new CustomerRequirementsResource($customer_requirements);

    }

    public function updateCustomerRequirement(object $payload, int $id)
    {
        $customer_requirements = $this->customer_requirementsRepository->update($payload, $id);

        return new CustomerRequirementsResource($customer_requirements);
    }

    public function deleteCustomerRequirement(int $id)
    {
        return $this->customer_requirementsRepository->delete($id);
    }
}
