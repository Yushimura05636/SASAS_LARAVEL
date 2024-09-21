<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentPermissionMapStoreRequest;
use App\Http\Requests\DocumentPermissionMapUpdateRequest;
use App\Interface\Service\DocumentPermissionMapServiceInterface;
use Illuminate\Http\Request;

class DocumentPermissionMapController extends Controller
{
    private $documentPermissionMapService;

    public function __construct(DocumentPermissionMapServiceInterface $documentPermissionMapService)
    {
        $this->documentPermissionMapService=$documentPermissionMapService;
    }

    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
        return $this->documentPermissionMapService->findDocumentPermissionMap();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentPermissionMapStoreRequest $request)
    {
        return $this->documentPermissionMapService->createDocumentPermissionMap($request);

    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->documentPermissionMapService->findDocumentPermissionMapById($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentPermissionMapUpdateRequest $request, int $id)
    {
        return $this->documentPermissionMapService->updateDocumentPermissionMap($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->documentPermissionMapService->deleteDocumentPermissionMap($id);

    }
}
