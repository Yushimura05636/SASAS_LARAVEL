<?php

namespace App\Http\Controllers;

use App\Interface\Service\RequirementServiceInterface;
use App\Models\Requirements;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequirementController extends Controller
{
    private $requirementService;

    public function __construct(RequirementServiceInterface $requirementService)
    {
        $this->requirementService = $requirementService;
    }

    public function index()
    {
        return $this->requirementService->findRequirements();
    }

    public function active()
    {
        //get only the active requirements
        $activeRequirement = Requirements::where('isActive', 1)->get();

        return new JsonResource($activeRequirement);
    }

    public function store(Request $request)
    {
        return $this->requirementService->createRequirement($request);
    }

    public function show(int $id)
    {
        return $this->requirementService->findRequirementById($id);
    }

    public function update(Request $request, int $id)
    {
        return $this->requirementService->updateRequirement($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->requirementService->deleteRequirement($id);
    }
}
