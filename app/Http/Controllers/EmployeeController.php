<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Interface\Service\EmployeeServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    private $employeeService;

    public function __construct(EmployeeServiceInterface $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index()
    {
        return $this->employeeService->findEmployees();
    }

    public function noUserIndex()
    {
        return $this->employeeService->findNoUserEmployees();
    }

    public function store(Request $request)
    {
        return $this->employeeService->createEmployee($request);
    }

    public function show(int $id)
    {
        return $this->employeeService->findEmlpoyeeById($id);
    }

    public function update(Request $request, int $id)
    {
        return $this->employeeService->updateEmployee($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->employeeService->deleteEmployee($id);
    }
}
