<?php

namespace App\Service;

use App\Http\Resources\SpouseResource;
use App\Interface\Repository\SpouseRepositoryInterface;
use App\Interface\Service\SpouseServiceInterface;

class SpouseService implements SpouseServiceInterface
{
    private $spouseRepository;

    public function __construct(SpouseRepositoryInterface $spouseRepository)
    {
        $this->spouseRepository = $spouseRepository;
    }

    public function findSpouses()
    {
        $spouse = $this->spouseRepository->findMany();

        return SpouseResource::collection($spouse);
    }

    public function findSpouseById(int $id)
    {
        $spouse = $this->spouseRepository->findOneById($id);

        return new SpouseResource($spouse);
    }

    public function findSpouseByEmail(string $email)
    {
        $spouse = $this->spouseRepository->findOneByEmail($email);

        return new SpouseResource($spouse);
    }

    public function createSpouse(object $payload)
    {
        $spouse = $this->spouseRepository->create($payload);

        return new SpouseResource($spouse);
    }

    public function updateSpouse(object $payload, int $id)
    {
        $spouse = $this->spouseRepository->update($payload, $id);

        return new SpouseResource($spouse);
    }

    public function deleteSpouse(int $id)
    {
        return $this->spouseRepository->delete($id);
    }
}
