<?php

namespace App\Service;

use App\Http\Resources\CustomerGroupResource;
use App\Interface\Repository\CustomerGroupRepositoryInterface;
use App\Interface\Service\CustomerGroupServiceInterface;

class CustomerGroupService implements CustomerGroupServiceInterface
{
    private $customerGroupRepository;

    public function __construct(CustomerGroupRepositoryInterface $customerGroupRepository)
    {
        $this->customerGroupRepository = $customerGroupRepository;
    }

    public function findCustomerGroup()
    {
        $customerGroup = $this->customerGroupRepository->findMany();

        return CustomerGroupResource::collection($customerGroup);
    }

    public function findCustomerGroupById(int $id)
    {
        $customerGroup = $this->customerGroupRepository->findOneById($id);

        return new CustomerGroupResource($customerGroup);
    }

    public function createCustomerGroup(object $payload)
    {
        $customerGroup = $this->customerGroupRepository->create($payload);

        return new CustomerGroupResource($customerGroup);
    }

    public function updateCustomerGroup(object $payload, int $id)
    {
        $customerGroup = $this->customerGroupRepository->update($payload, $id);

        return new CustomerGroupResource($customerGroup);
    }

    public function deleteCustomerGroup(int $id)
    {
        return $this->customerGroupRepository->delete($id);
    }
}
