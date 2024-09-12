<?php

namespace App\Interface\Repository;

interface DBLibraryRepositoryInterface
{
    public function findMany(string $modeltype);

    public function findOneById(string $modeltype, int $id);

    public function create(object $payload);

    public function update(int $id, object $payload);

    public function delete(object $payload, int $id);
}
