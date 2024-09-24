<?php

namespace App\Http\Controllers;

use App\Interface\Service\CustomerGroupServiceInterface;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    private $customerGroupService;

    public function __construct(CustomerGroupServiceInterface $customerGroupService)
    {
        $this->customerGroupService = $customerGroupService;
    }

    public function index()
    {
        return $this->customerGroupService->findCustomerGroup();
    }

    public function store(Request $request)
    {
        return $this->customerGroupService->createCustomerGroup($request);
    }

    public function show(int $id)
    {
        return $this->customerGroupService->findCustomerGroupById($id);
    }

    public function update(Request $request, int $id)
    {
        return $this->customerGroupService->updateCustomerGroup($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->customerGroupService->deleteCustomerGroup($id);
    }
}
