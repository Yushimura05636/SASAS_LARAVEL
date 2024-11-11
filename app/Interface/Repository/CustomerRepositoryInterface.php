<?php

namespace App\Interface\Repository;

interface CustomerRepositoryInterface
{
    public function findMany();

    public function findOneById($id);

    public function findOneByEmail(string $email);

    public function create(object $payload);

    public function update(object $payload, int $id);

    public function delete(int $id);

    public function findByGroupId($id);
    
    public function findByPersonalityId($personalityId);
    
}
