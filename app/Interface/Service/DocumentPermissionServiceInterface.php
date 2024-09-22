<?php

namespace App\Interface\Service;

interface DocumentPermissionServiceInterface
{
    public function findDocumentPermission();

    public function findDocumentPermissionById(int $id);

    public function createDocumentPermission(object $payload);

    public function updateDocumentPermission(object $payload, int $id);

    public function deleteDocumentPermission(int $id);
}
