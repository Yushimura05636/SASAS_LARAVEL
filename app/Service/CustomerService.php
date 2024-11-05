<?php

namespace App\Service;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerResourceCollection;
use App\Interface\Repository\CustomerRepositoryInterface;
use App\Interface\Service\CustomerServiceInterface;
use App\Models\Customer;

class CustomerService implements CustomerServiceInterface
{
    private $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function findCustomers()
    {
        $customer = $this->customerRepository->findMany();

        return CustomerResource::collection($customer);
    }

    public function findCustomerById(int $id)
    {
        $customer = $this->customerRepository->findOneById($id);

        return new CustomerResource($customer);
    }

    public function findCustomerByEmail(string $email)
    {
        $customer = $this->customerRepository->findOneByEmail($email);

        return new CustomerResource($customer);
    }

    public function createCustomer(object $payload)
    {
        $customer = $this->customerRepository->create($payload);

        return new CustomerResource($customer);
    }

    public function updateCustomer(object $payload, int $id)
    {
        $customer = $this->customerRepository->update($payload, $id);

        return new CustomerResource($customer);
    }

    public function deleteCustomer(int $id)
    {
        return $this->customerRepository->delete($id);
    }

    public function findCustomerByGroupId(int $id)
    {
        $customer = $this->customerRepository->findByGroupId($id);
        return new CustomerResourceCollection($customer);
    }
}
