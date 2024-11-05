<?php

namespace App\Interface\Repository;

interface SpouseRepositoryInterface
{
    public function findMany();

    public function findOneById(int $id);

    public function findOneByEmail(string $email);

    public function create(object $payload);

    public function update(object $payload, int $id);

    public function delete(int $id);
}
