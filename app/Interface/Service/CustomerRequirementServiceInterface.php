<?php

namespace App\Interface\Service;

interface CustomerRequirementServiceInterface
{
    public function findCustomerRequirements();

    public function findCustomerRequirementById(int $id);

    public function createCustomerRequirement(object $payload);

    public function updateCustomerRequirement(object $payload, int $id);

    public function deleteCustomerRequirement(int $id);
}
