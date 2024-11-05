<?php

namespace App\Interface\Repository;

interface LoanApplicationRepositoryInterface
{
    public function findMany();

    public function findOneById(int $id);

    public function create(object $payload);

    public function update(object $payload, int $id);

    public function delete(int $id);
}
