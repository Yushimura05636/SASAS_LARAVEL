<?php

namespace App\Http\Middleware;

use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use App\Models\Permission;  // Assuming you have a Permission model
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
    public function handle(Request $request, Closure $next)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Get the user ID from the authenticated user
        $userId = auth()->user()->id;

        // Retrieve docId and perm from the request (either POST body or GET parameters)
        $documentId = $request->input('docId');
        $requiredPermission = $request->input('perm', 'view'); // Default to 'view' if not provided

        // Clean up permission string
        $requiredPermission = trim($requiredPermission);

        // Fetch the dynamic permission mapping from the database
        $permission = Document_Permission_Map::where('description', $requiredPermission)->first();

        // If no permission mapping is found in the database, default to view permission
        if (!$permission) {
            return response()->json(['message' => 'Invalid permission type'], 400);
        }

        $requiredPermissionValue = $permission->id; // Assuming `id` is the numeric value of permission

        // Query the Document_Permission table to check user's permissions for the document
        $userPermission = Document_Permission::where('user_id', $userId)
            ->where('document_map_code', $documentId)
            ->where('document_permission', $requiredPermissionValue)
            ->first();

        // Check if the user's permission level is sufficient for the requested action
        if (!$userPermission || $userPermission->document_permission < $requiredPermissionValue) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        // Proceed if user has the necessary permission
        return $next($request);
    }
}
