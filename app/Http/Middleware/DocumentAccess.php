<?php

namespace App\Http\Middleware;

use App\Models\Document_Map;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DocumentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $requiredPermissionValue  The numeric permission value (e.g. 4 for view)
     * @param  int  $requiredDocId            The document ID
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $requiredDocId, $requiredPermissionValue)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Get the user ID from the authenticated user
        $userId = auth()->user()->id;

        if($request->input('docId') && $request->input('perm'))
        {
           // Retrieve docId and perm from the request (either POST body or GET parameters)
            $documentId = $request->input('docId');
            $clientSentPermissionValue = $request->input('perm'); // Client-sent permission (e.g., 4)
        }

        $requiredPermissionValue = (int) trim($requiredPermissionValue);
        $requiredDocId = (int) trim($requiredDocId);

        // Fetch the dynamic permission mapping from the database (client-sent)
        $permission = Document_Permission_Map::where('id', $requiredPermissionValue)->first();
        $document = Document_Map::where('id', $requiredDocId)->first();

        //return response()->json([$requiredPermissionValue, $requiredDocId, $permission, $document]);

        //if the document permission exist in database
        if($permission && $document)
        {
            //if ang client side nag throw ug docId and perm kini ang mu read
            if($request->input('docId') && $request->input('perm'))
            {
                // Check if client-sent permission and docId match the numeric middleware parameters
                if ($clientSentPermissionValue != $requiredPermissionValue || $documentId != $requiredDocId) {
                    return response()->json([
                        'message' => 'Client-sent permission or document ID does not match the required permission'
                    ], Response::HTTP_FORBIDDEN);
                }
            }

            // Query the Document_Permission table to check user's permissions for the document
            $userPermission = Document_Permission::where('user_id', $userId)
            ->where('document_map_code', $requiredDocId)
            ->where('document_permission', $requiredPermissionValue) // Ensure user's permission meets required level
            ->first();

            // return response()->json(['message' => $userPermission]);


            // If no matching permission is found, deny access
            if (!$userPermission) {
                return response()->json(['message' => 'Access Denied'], Response::HTTP_FORBIDDEN);
            }

            // Proceed if user has the necessary permission
            return $next($request);
        }
        else
        {
            return response()->json(['message' => 'Missing permission or document'], Response::HTTP_FORBIDDEN);
        }
    }
}
