<?php

namespace App\Interface\Service;

interface RequirementServiceInterface
{
    public function findRequirements();

    public function findRequirementById(int $id);

    public function createRequirement(object $payload);

    public function updateRequirement(object $payload, int $id);

    public function deleteRequirement(int $id);
}
