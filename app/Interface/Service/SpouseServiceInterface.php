<?php

namespace App\Interface\Service;

interface SpouseServiceInterface
{
    public function findSpouses();

    public function findSpouseById(int $id);

    public function findSpouseByEmail(string $email);

    public function createSpouse(object $payload);

    public function updateSpouse(object $payload, int $id);

    public function deleteSpouse(int $id);
}
