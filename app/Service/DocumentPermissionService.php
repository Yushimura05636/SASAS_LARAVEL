<?php

namespace App\Service;

use App\Http\Resources\DocumentPermissionResource;
use App\Interface\Service\DocumentPermissionServiceInterface;
use App\Repository\DocumentPermissionRepository;

class DocumentPermissionService implements DocumentPermissionServiceInterface
{


    private $documentPermissionRepository;

    public function __construct(DocumentPermissionRepository $documentPermissionRepository)
    {
        $this->documentPermissionRepository = $documentPermissionRepository;
    }

    public function findDocumentPermission()
    {
        $documentPermission = $this->documentPermissionRepository->findMany();
        return DocumentPermissionResource::collection($documentPermission);

    }

    public function findDocumentPermissionById(int $id)
    {
        $documentPermission = $this->documentPermissionRepository->findOneById($id);
        return new DocumentPermissionResource($documentPermission);

    }

    public function createDocumentPermission(object $payload)
    {
        $documentPermission = $this->documentPermissionRepository->create($payload);
        return new DocumentPermissionResource($documentPermission);

    }

    public function updateDocumentPermission(object $payload, int $id)
    {
        $documentPermission = $this->documentPermissionRepository->update($payload, $id);
        return new DocumentPermissionResource($documentPermission);

    }

    public function deleteDocumentPermission(int $id)
    {
        return $this->documentPermissionRepository->delete($id);

    }
}
