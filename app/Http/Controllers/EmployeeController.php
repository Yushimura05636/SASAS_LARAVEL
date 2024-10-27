<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Interface\Service\EmployeeServiceInterface;
use App\Models\Employee;
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
        // // Get employees who do not have an associated user
        // return $employeesWithoutUsers = Employee::leftJoin('user_account as user', 'employee.id', '=', 'user.employee_id')
        //     ->whereNull('user.employee_id')  // Filter where employee_id in user is NULL
        //     ->select('employee.*')  // Select only employee fields
        //     ->get();
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
