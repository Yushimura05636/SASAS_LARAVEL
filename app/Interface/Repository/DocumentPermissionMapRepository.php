<?php

namespace App\Interface\Repository;

interface DocumentPermissionMapRepository
{
    public function findMany();

    public function findOneById($id);

    public function findOneByValue(string $value);

    public function create(object $payload);

    public function update(object $payload, int $id);

    public function delete(int $id);
}
