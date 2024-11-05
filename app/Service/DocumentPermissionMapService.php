<?php

namespace App\Service;

use App\Http\Resources\DocumentPermissionMapResource;
use App\Interface\Repository\DocumentPermissionMapRepositoryInterface;
use App\Interface\Service\DocumentPermissionMapServiceInterface;

class DocumentPermissionMapService implements DocumentPermissionMapServiceInterface
{


    private $documentPermissionMapRepository;

    public function __construct(DocumentPermissionMapRepositoryInterface $documentPermissionMapRepository)
    {
        $this->documentPermissionMapRepository = $documentPermissionMapRepository;
    }

    public function findDocumentPermissionMap()
    {
        $documentMap = $this->documentPermissionMapRepository->findMany();
        return DocumentPermissionMapResource::collection($documentMap);

    }

    public function findDocumentPermissionMapById(int $id)
    {
        $documentMap = $this->documentPermissionMapRepository->findOneById($id);
        return new DocumentPermissionMapResource($documentMap);

    }

    public function findDocumentPermissionMapByValue(string $value)
    {
        $documentMap = $this->documentPermissionMapRepository->findOneByValue($value);
        return new DocumentPermissionMapResource($documentMap);
    }

    public function createDocumentPermissionMap(object $payload)
    {
        $documentMap = $this->documentPermissionMapRepository->create($payload);
        return new DocumentPermissionMapResource($documentMap);

    }

    public function updateDocumentPermissionMap(object $payload, int $id)
    {
        $documentMap = $this->documentPermissionMapRepository->update($payload, $id);
        return new DocumentPermissionMapResource($documentMap);

    }

    public function deleteDocumentPermissionMap(int $id)
    {
        return $this->documentPermissionMapRepository->delete($id);

    }
}
