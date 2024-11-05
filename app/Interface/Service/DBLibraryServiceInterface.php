<?php

namespace App\Interface\Service;

interface DBLibraryServiceInterface
{
    public function findEntries(string $modeltype);

    public function findEntry(string $modeltype, int $id);

    public function createEntry(object $payload);

    public function updateEntryById(object $payload, int $id);

    public function deleteEntryById(object $modeltype, int $id);
}
