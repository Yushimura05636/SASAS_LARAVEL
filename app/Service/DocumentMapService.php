<?php

namespace App\Service;

use App\Http\Resources\DocumentMapResource;
use App\Interface\Repository\DocumentMapRepositoryInterface;
use App\Interface\Service\DocumentMapServiceInterface;

class DocumentMapService implements DocumentMapServiceInterface
{


    private $documentMapRepository;

    public function __construct(DocumentMapRepositoryInterface $documentMapRepository)
    {
        $this->documentMapRepository = $documentMapRepository;
    }

    public function findDocumentMap()
    {
        $documentMap = $this->documentMapRepository->findMany();

        return DocumentMapResource::collection($documentMap);

    }

    public function findDocumentMapById(int $id)
    {
        $documentMap = $this->documentMapRepository->findOneById($id);
        return new DocumentMapResource($documentMap);

    }

    public function findDocumentMapByValue(string $value)
    {
        $documentMap = $this->documentMapRepository->findOneByValue($value);
        return new DocumentMapResource($documentMap);
    }

    public function createDocumentMap(object $payload)
    {
        $documentMap = $this->documentMapRepository->create($payload);
        return new DocumentMapResource($documentMap);

    }

    public function updateDocumentMap(object $payload, int $id)
    {
        $documentMap = $this->documentMapRepository->update($payload, $id);
        return new DocumentMapResource($documentMap);

    }

    public function deleteDocumentMap(int $id)
    {
        return $this->documentMapRepository->delete($id);

    }
}
