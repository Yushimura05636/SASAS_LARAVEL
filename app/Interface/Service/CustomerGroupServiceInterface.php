<?php 

namespace App\Interface\Service;

interface CustomerGroupServiceInterface
{
    public function findCustomerGroup();

    public function findCustomerGroupById(int $id);

    public function createCustomerGroup(object $payload);

    public function updateCustomerGroup(object $payload, int $id);

    public function deleteCustomerGroup(int $id);
}