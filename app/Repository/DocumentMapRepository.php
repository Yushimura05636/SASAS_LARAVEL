<?php

namespace App\Repository;

use App\Interface\Repository\DocumentMapRepositoryInterface;
use App\Models\Document_Map;
use Illuminate\Http\Response;

class DocumentMapRepository implements DocumentMapRepositoryInterface
{
    public function findMany()
    {
        return Document_Map::get();
    }

    public function findOneById($id)
    {
        return Document_Map::findOrFail($id);
    }

    public function findOneByValue(string $value)
    {
        return Document_Map::where('description', $value)->first();
    }

    public function create(object $payload)
    {
        $documentMap = new Document_Map();
        $documentMap->description = $payload->description;
        $documentMap->save();
        return $documentMap->refresh();
    }

    public function update(object $payload, int $id)
    {
        $documentMap = Document_Map::findOrFail($id);
        $documentMap->description = $payload->description;
        $documentMap->save();
        return $documentMap->refresh();
    }

    public function delete(int $id)
    {
        $documentMap = Document_Map::findOrFail($id);
        $documentMap->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
