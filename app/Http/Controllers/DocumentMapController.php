<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentMapStoreRequest;
use App\Http\Requests\DocumentMapUpdateRequest;
use App\Interface\Service\DocumentMapServiceInterface;
use Illuminate\Http\Request;

class DocumentMapController extends Controller
{
    private $documentMapService;

    public function __construct(DocumentMapServiceInterface $documentMapService)
    {
        $this->documentMapService=$documentMapService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->documentMapService->findDocumentMap();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentMapStoreRequest $request)
    {
        return $this->documentMapService->createDocumentMap($request);

    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->documentMapService->findDocumentMapById($id);

    }

    public function look(string $value)
    {
        return $this->documentMapService->findDocumentMapByValue($value);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentMapUpdateRequest $request, int $id)
    {
        return $this->documentMapService->updateDocumentMap($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->documentMapService->deleteDocumentMap($id);

    }
}
