<?php

namespace App\Repository;

use App\Interface\Repository\DocumentPermissionMapRepositoryInterface;
use App\Models\Document_Permission_Map;
use Illuminate\Http\Response;

class DocumentPermissionMapRepository implements DocumentPermissionMapRepositoryInterface
{
    public function findMany()
    {
        return Document_Permission_Map::get();
    }

    public function findOneById($id)
    {
        return Document_Permission_Map::findOrFail($id);
    }

    public function findOneByValue(string $value)
    {
        return Document_Permission_Map::where('description', $value)->first();
    }

    public function create(object $payload)
    {
        $documentPermissionMap = new Document_Permission_Map();
        $documentPermissionMap->description = $payload->description;
        $documentPermissionMap->save();
        return $documentPermissionMap->refresh();
    }

    public function update(object $payload, int $id)
    {
        $documentPermissionMap = Document_Permission_Map::findOrFail($id);
        $documentPermissionMap->description = $payload->description;
        $documentPermissionMap->save();
        return $documentPermissionMap->refresh();
    }

    public function delete(int $id)
    {
        $documentPermissionMap = Document_Permission_Map::findOrFail($id);
        $documentPermissionMap->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
