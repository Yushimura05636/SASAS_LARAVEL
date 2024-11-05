<?php

namespace App\Service;

use App\Http\Resources\RequirementsResource;
use App\Interface\Repository\RequirementRepositoryInterface;
use App\Interface\Service\RequirementServiceInterface;

class RequirementService implements RequirementServiceInterface
{
    private $requirementsRepository;

    public function __construct(RequirementRepositoryInterface $requirementsRepository)
    {
        $this->requirementsRepository = $requirementsRepository;
    }

    public function findRequirements()
    {
        $requirements = $this->requirementsRepository->findMany();

        return RequirementsResource::collection($requirements);
    }

    public function findRequirementById(int $id)
    {
        $requirements = $this->requirementsRepository->findOneById($id);

        return new RequirementsResource($requirements);

    }

    public function createRequirement(object $payload)
    {
        $requirements = $this->requirementsRepository->create($payload);

        return new RequirementsResource($requirements);

    }

    public function updateRequirement(object $payload, int $id)
    {
        $requirements = $this->requirementsRepository->update($payload, $id);

        return new RequirementsResource($requirements);
    }

    public function deleteRequirement(int $id)
    {
        return $this->requirementsRepository->delete($id);
    }
}
