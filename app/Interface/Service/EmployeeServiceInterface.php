<?php

namespace App\Interface\Service;

interface EmployeeServiceInterface
{
    public function findEmployees();

    public function findNoUserEmployees();

    public function findEmlpoyeeById(int $id);

    public function createEmployee(object $payload);

    public function updateEmployee(object $payload, int $id);

    public function deleteEmployee(int $id);
}
