<?php

namespace App\Interface\Repository;


interface EmployeeRepositoryInterface
{
    public function findByMany();

    public function findNoUserByMany();

    public function findByOneId(int $id);

    public function create(object $payload);

    public function update(object $payload, int $id);

    public function delete(int $id);
}
