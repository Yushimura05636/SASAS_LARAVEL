<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Interface\Service\CustomerServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    private $customerService;

    public function __construct(CustomerServiceInterface $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index()
    {
        return $this->customerService->findCustomers();
    }

    public function store(Request $request)
    {
        return $this->customerService->createCustomer($request);
    }

    public function show(int $id)
    {
        return $this->customerService->findCustomerById($id);
    }

    public function update(Request $request, int $id)
    {
        return $this->customerService->updateCustomer($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->customerService->deleteCustomer($id);
    }
}
