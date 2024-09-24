<?php

namespace App\Http\Middleware;

use App\Models\Document_Map;
use App\Models\Document_Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DocumentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, $documentId, $requiredPermission = 'view')
    {
        if (!Auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userId = auth()->user()->id;

        // Assuming $requiredPermission might have leading/trailing spaces
        $requiredPermission = trim($requiredPermission);

        $permissionMap = [
            'create' => 1,
            'view' => 4,   // Updated to 4 for reading
            'update' => 2,
            'delete' => 3,
            'assisst' => 5,
        ];

        // If stored as a comma-separated string
        //$userPermissions = explode(',', $requiredPermission->document_permission);

        // Get the required permission numeric value based on the passed permission
        $requiredPermissionValue = $permissionMap[$requiredPermission] ?? 4;  // Default to 'read'

        // Query the database to check if the user has permission for this document
        $permission = Document_Permission::where('user_id', $userId)
            ->where('document_map_code', $documentId)
            ->first();

//         // Log the permission data for debugging purposes
// return response()->json([
//     'user' => $userId,
//     'doc' => $documentId,
//     'required_permission' => $requiredPermissionValue,
//     'user_permissions' => $permission->document_permission,
//     'req' => $requiredPermission,
// ], Response::HTTP_INTERNAL_SERVER_ERROR);


        // Check if the user's permission value is greater than or equal to the required permission
        if (!$permission || $permission->document_permission < $requiredPermissionValue) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        return $next($request);
    }
}
