<?php

namespace App\Http\Controllers;

use App\Interface\Service\CustomerRequirementServiceInterface;
use Illuminate\Http\Request;

class CustomerRequirementController extends Controller
{
    private $customerRequirementService;

    public function __construct(CustomerRequirementServiceInterface $customerRequirementService)
    {
        $this->customerRequirementService = $customerRequirementService;
    }

    public function index()
    {
        return $this->customerRequirementService->findCustomerRequirements();
    }

    public function show(int $id)
    {
        return $this->customerRequirementService->findCustomerRequirementById($id);
    }

    public function create(Request $request)
    {
        return $this->customerRequirementService->createCustomerRequirement($request);
    }

    public function update(Request $request, int $id)
    {
        return $this->customerRequirementService->updateCustomerRequirement($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->customerRequirementService->deleteCustomerRequirement($id);
    }
}
