<?php

namespace App\Interface\Service;

interface PersonalityServiceInterface
{
    public function findPersonality();

    public function findPersonalityById(int $id);

    public function createPersonality(object $payload);

    public function updatePersonality(object $payload, int $id);

    public function deletePersonality(int $id);
}
