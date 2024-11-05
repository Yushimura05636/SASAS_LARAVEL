<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentPermissionStoreRequest;
use App\Http\Requests\DocumentPermissionUpdateRequest;
use App\Http\Resources\DocumentPermissionResource;
use App\Interface\Service\DocumentPermissionServiceInterface;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
    public function update(Request $request, int $id, DocumentPermissionMapController $documentPermissionMapController)
{
    // Start the transaction
    DB::beginTransaction();

    try {
        // Get the user data and document permission map from the request
        $user = $request->input('User');
        $documentPermissions = $request->input('DocumentPermissionMap.data');

        // Fetch all existing permissions for this user and document_map_code from the database
        $existingPermissions = Document_Permission::where('user_id', $user['id'])->pluck('id')->toArray();

        // Extract the IDs from the JSON data that the user submitted
        $jsonIds = [];
        foreach ($documentPermissions as $document) {
            $jsonIds[] = $document['document_id'];
        }

        // Find the IDs that are in the database but not in the JSON (extras)
        $extraIds = array_diff($existingPermissions, $jsonIds);

        // Delete the records that are not present in the JSON
        if (!empty($extraIds)) {
            Document_Permission::whereIn('id', $extraIds)->delete();
        }

        // Process the document permissions from the JSON (insert/update logic)
        foreach ($documentPermissions as $document) {
            $documentId = $document['document_id'];
            $permissions = $document['permissions']; // e.g., ['view', 'create']
            $dateGranted = $document['datetime_granted'];

            // Loop through the permissions for each document
            foreach ($permissions as $permission) {
                $mapPerm = $documentPermissionMapController->look($permission);

                if ($mapPerm) {
                    $payload = [
                        'user_id' => $user['id'],
                        'document_map_code' => $documentId,
                        'document_permission' => $mapPerm->id,
                        'datetime_granted' => $dateGranted,
                    ];

                    // Insert or update the record as necessary
                    Document_Permission::updateOrCreate(
                        ['user_id' => $user['id'], 'document_map_code' => $documentId, 'document_permission' => $mapPerm->id],
                        $payload
                    );
                }
            }
        }

        // Step to remove duplicates: Find and delete any duplicates after the update.
        $this->removeDuplicates($user['id']);

        // Commit the transaction
        DB::commit();

        return [
            'message' => 'Permissions successfully updated for user.',
            'user' => $user,
        ];

    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        DB::rollBack();

        return response()->json([
            'message' => 'Failed to update document permissions.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Helper function to remove duplicates.
 * This function scans for duplicates in the Document_Permission table based on user_id, document_map_code, and document_permission.
 */
private function removeDuplicates($userId)
{
    // Get all the records for this user, grouped by document_map_code and document_permission
    $duplicates = Document_Permission::select('user_id', 'document_map_code', 'document_permission', DB::raw('COUNT(*) as count'))
        ->where('user_id', $userId)
        ->groupBy('user_id', 'document_map_code', 'document_permission')
        ->having('count', '>', 1)
        ->get();

    // Loop over each set of duplicates and delete the extras
    foreach ($duplicates as $duplicate) {
        // Fetch all records with the same user_id, document_map_code, and document_permission
        $duplicateRecords = Document_Permission::where('user_id', $duplicate->user_id)
            ->where('document_map_code', $duplicate->document_map_code)
            ->where('document_permission', $duplicate->document_permission)
            ->orderBy('id') // Order by ID to keep the first occurrence
            ->get();

        // Skip the first record and delete the rest
        $duplicateRecords->shift(); // Remove the first occurrence
        foreach ($duplicateRecords as $record) {
            $record->delete(); // Delete the duplicate record
        }
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
