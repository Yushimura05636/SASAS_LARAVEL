<?php

namespace App\Http\Controllers;


use App\Http\Requests\FactorRateStoreRequest;
use App\Http\Requests\FactorRateUpdateRequest;
use App\Interface\Service\FactorRateServiceInterface;

class FactorRateController extends Controller
{
    private $factorRateService;

    public function __construct(FactorRateServiceInterface $factorRateService)
    {
        $this->factorRateService=$factorRateService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->factorRateService->findFactorRate();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FactorRateStoreRequest $request)
    {
        return $this->factorRateService->createFactorRate($request);

    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->factorRateService->findFactorRateById($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FactorRateUpdateRequest $request, int $id)
    {
        return $this->factorRateService->updateFactorRate($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->factorRateService->deleteFactorRate($id);

    }
}
