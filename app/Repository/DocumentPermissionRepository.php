<?php

namespace App\Repository;

use App\Interface\Repository\DocumentPermissionRepositoryInterface;
use App\Models\Document_Permission;
use Illuminate\Http\Response;

class DocumentPermissionRepository implements DocumentPermissionRepositoryInterface
{
    public function findMany()
    {
        return Document_Permission::paginate(10);
    }

    public function findOneById($id)
    {
        return Document_Permission::findOrFail($id);
    }

    public function findOneByValue(string $value)
    {
        return Document_Permission::where('description', $value);
    }

    public function create(object $payload)
    {
        $documentPermission = new Document_Permission();
        $documentPermission->user_id = $payload->user_id;
        $documentPermission->document_map_code = $payload->document_id;
        $documentPermission->document_permission = $payload->permission_id;
        $documentPermission->datetime_granted = $payload->datetime_granted;
        $documentPermission->save();
        return $documentPermission->refresh();
    }

    public function update(object $payload, int $id)
    {
        $documentPermission = Document_Permission::findOrFail($id);
        $documentPermission->user_id = $payload->user_id;
        $documentPermission->document_map_code = $payload->document_map_code;
        $documentPermission->document_permission = $payload->document_permission;
        $documentPermission->datetime_granted = $payload->datetime_granted;
        $documentPermission->save();
        return $documentPermission->refresh();
    }

    public function delete(int $id)
    {
        $documentPermission = Document_Permission::findOrFail($id);
        $documentPermission->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
