<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentPermissionStoreRequest;
use App\Http\Requests\DocumentPermissionUpdateRequest;
use App\Interface\Service\DocumentPermissionServiceInterface;
use App\Models\Document_Permission_Map;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentPermissionController extends Controller
{
    private $documentPermissionService;

    public function __construct(DocumentPermissionServiceInterface $documentPermissionService)
    {
        $this->documentPermissionService=$documentPermissionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->documentPermissionService->findDocumentPermission();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DocumentPermissionMapController $documentPermissionMapController, DocumentMapController $documentMapController)
    {
        // Start the transaction
        DB::beginTransaction();

        try {
            $user = $request->input('User');
            $documentPermissions = $request->input('DocumentPermissionMap.data');

            // First loop: iterate over the documents
            foreach ($documentPermissions as $document) {
                $documentId = $document['document_id'];
                $permissions = $document['permissions']; // e.g., ['view', 'create']
                $dateGranted = $document['datetime_granted'];

                // Second loop: iterate over the permissions for each document
                foreach ($permissions as $permission) {
                    $mapPerm = $documentPermissionMapController->look($permission);

                    if ($mapPerm) {
                        $payload = [
                            'user_id' => $user['id'],
                            'document_id' => $documentId,
                            'permission_id' => $mapPerm->id,
                            'datetime_granted' => $dateGranted,
                        ];

                        // Cast the array to an object if needed
                        $payloadObject = (object) $payload;

                        // Call the createDocumentPermission method to insert into the DB
                        $this->documentPermissionService->createDocumentPermission($payloadObject);
                    }
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return [
                'message' => 'Permissions successfully stored for user.',
                'user' => $user,
            ];

        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Return an error message with the exception
            return response()->json([
                'message' => 'Failed to store document permissions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->documentPermissionService->findDocumentPermissionById($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id, DocumentPermissionMapController $documentPermissionMapController, DocumentMapController $documentMapController)
    {
        // Start the transaction
        DB::beginTransaction();

        try {
            $user = $request->input('User');
            $documentPermissions = $request->input('DocumentPermissionMap.data');

            // First loop: iterate over the documents
            foreach ($documentPermissions as $document) {
                $documentId = $document['document_id'];
                $permissions = $document['permissions']; // e.g., ['view', 'create']

                // Second loop: iterate over the permissions for each document
                foreach ($permissions as $permission) {
                    $mapPerm = $documentPermissionMapController->look($permission);

                    if ($mapPerm) {
                        $payload = [
                            'user_id' => $user['id'],
                            'document_id' => $documentId,
                            'permission_id' => $mapPerm->id,
                        ];

                        // Cast the array to an object if needed
                        $payloadObject = (object) $payload;

                        // Call the createDocumentPermission method to insert into the DB
                        $this->documentPermissionService->updateDocumentPermission($payloadObject, $id);
                    }
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return [
                'message' => 'Permissions successfully stored for user.',
                'user' => $user,
            ];

        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Return an error message with the exception
            return response()->json([
                'message' => 'Failed to store document permissions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->documentPermissionService->deleteDocumentPermission($id);

    }
}
