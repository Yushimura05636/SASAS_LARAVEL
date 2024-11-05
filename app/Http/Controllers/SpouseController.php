<?php

namespace App\Http\Controllers;
use App\Http\Requests\SpouseStoreRequest;
use App\Http\Requests\SpouseUpdateRequest;
use App\Interface\Service\SpouseServiceInterface;
use Illuminate\Http\Request;

class SpouseController extends Controller
{

    private $spouseService;
    public function __construct(SpouseServiceInterface $spouseService)
    {

        $this->spouseService = $spouseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->spouseService->findSpouses();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpouseStoreRequest $request)
    {
        return $this->spouseService->createSpouse($request);

    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->spouseService->findSpouseById($id);

    }

    public function showEmail(string $email)
    {
        return $this->spouseService->findSpouseByEmail($email);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpouseUpdateRequest $request, int $id)
    {
        return $this->spouseService->updateSpouse($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->spouseService->deleteSpouse($id);

    }
}
