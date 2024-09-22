<?php

namespace App\Interface\Service;

interface DocumentPermissionMapServiceInterface
{
    public function findDocumentPermissionMap();

    public function findDocumentPermissionMapById(int $id);

    public function findDocumentPermissionMapByValue(string $value);

    public function createDocumentPermissionMap(object $payload);

    public function updateDocumentPermissionMap(object $payload, int $id);

    public function deleteDocumentPermissionMap(int $id);
}
