<?php

namespace App\Interface\Service;

interface DocumentMapServiceInterface
{
    public function findDocumentMap();

    public function findDocumentMapById(int $id);

    public function findDocumentMapByValue(string $value);

    public function createDocumentMap(object $payload);

    public function updateDocumentMap(object $payload, int $id);

    public function deleteDocumentMap(int $id);
}
