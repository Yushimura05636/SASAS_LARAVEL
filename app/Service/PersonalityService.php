<?php

namespace App\Service;

use App\Http\Resources\PersonalityResource;
use App\Interface\Repository\PersonalityRepositoryInterface;
use App\Interface\Service\PersonalityServiceInterface;
use Illuminate\Http\Response;

class PersonalityService implements PersonalityServiceInterface
{
    private $personalityRepository;

    public function __construct(PersonalityRepositoryInterface $personalityRepository)
    {
        $this->personalityRepository = $personalityRepository;
    }

    public function findPersonality()
    {
        $personality =$this->personalityRepository->findMany();
        return PersonalityResource::collection($personality);
    }

    public function findPersonalityById(int $id)
    {
        $personality =$this->personalityRepository->findOneById($id);
        return new PersonalityResource($personality);
    }

    public function createPersonality(object $payload)
    {
        $personality =$this->personalityRepository->create($payload);
        return new PersonalityResource($personality);
    }

    public function updatePersonality(object $payload, int $id)
    {
        $personality =$this->personalityRepository->update($payload, $id);
        return new PersonalityResource($personality);
    }

    public function deletePersonality(int $id)
    {
        return $this->personalityRepository->delete($id);
    }
}
