<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Requests\PersonalityStoreRequest;
use App\Http\Requests\PersonalityUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\PersonalityResource;
use App\Interface\Service\CustomerServiceInterface;
use App\Interface\Service\EmployeeServiceInterface;
use App\Interface\Service\PersonalityServiceInterface;
use App\Models\Personality;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeePersonalityController extends Controller
{
    private $employeeService;
    private $personalityService;

    public function __construct(EmployeeServiceInterface $employeeService, PersonalityServiceInterface $personalityServiceInterface)
    {
        $this->employeeService = $employeeService;
        $this->personalityService = $personalityServiceInterface;
    }

    public function index()
    {
    // Fetch employees and personalities from their respective services
    $employees = $this->employeeService->findEmployees(); // Assuming findCustomers returns employees
    $personalities = $this->personalityService->findPersonality();

    // Create an associative array (lookup) for personalities using personality_id as key
    $personalityMap = [];
    foreach ($personalities as $personality) {
        $personalityMap[$personality->id] = $personality;
    }

    // Loop through employees and pair them with their respective personality
    $employeesWithPersonality = [];
    foreach ($employees as $employee) {
        $personalityId = $employee->personality_id;
        // Find the corresponding personality using the personality_id
        $personality = $personalityMap[$personalityId] ?? null;

        // Pair the employee with their personality
        $employeesWithPersonality[] = [
            'employee' => $employee,
            'personality' => $personality
        ];
    }

    // Return the paired employees and personalities
    return [
        'data' => $employeesWithPersonality
    ];
}

public function look()
    {
    // Fetch employees and personalities from their respective services
    $employees = $this->employeeService->findNoUserEmployees(); // Assuming findCustomers returns employees
    $personalities = $this->personalityService->findPersonality();

    //return response()->json(['message' => [$employees,$personalities]]);

    // Create an associative array (lookup) for personalities using personality_id as key
    $personalityMap = [];
    foreach ($personalities as $personality) {
        $personalityMap[$personality->id] = $personality;
    }

    // Loop through employees and pair them with their respective personality
    $employeesWithPersonality = [];
    foreach ($employees as $employee) {
        $personalityId = $employee->personality_id;
        // Find the corresponding personality using the personality_id
        $personality = $personalityMap[$personalityId] ?? null;

        // Pair the employee with their personality
        $employeesWithPersonality[] = [
            'employee' => $employee,
            'personality' => $personality
        ];
    }

    // Return the paired employees and personalities
    return [
        'data' => $employeesWithPersonality
    ];
}

    public function store(Request $request, EmployeeController $employeeController, PersonalityController $personalityController)
    {
        // Summons the storeRequest from both controllers
        $employeeStoreRequest = new EmployeeStoreRequest();
        $personalityStoreRequest = new PersonalityStoreRequest();

        // Access the customer and personality data
        $employeeData = $request->input('employee');
        $personalityData = $request->input('personality');

        // Merge data for validation
        $datas = array_merge($employeeData, $personalityData);
        $rules = array_merge($employeeStoreRequest->rules(), $personalityStoreRequest->rules());

        // Validate data
        $validate = Validator::make($datas, $rules);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation error!',
                'data' => $datas,
                'error' => $validate->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            // First, store the personality
            $personalityResponse = $personalityController->store(new Request($personalityData));

            // Attempt to find the personality by first name, family name, and middle name
            $personality = Personality::where('first_name', $personalityData['first_name'])
                ->where('family_name', $personalityData['family_name'])
                ->where('middle_name', $personalityData['middle_name'])
                ->firstOrFail(); // This will throw an exception if not found

            // Get the ID of the found personality
            $id = $personality->id;

            // Then put the ID to personality_id in customer
            $employeeData['personality_id'] = $id;
            $employeeResponse = $employeeController->store(new Request($employeeData));

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Customer and Personality saved successfully',
                'customer' => new EmployeeResource($employeeResponse), // Use resource class
                'personality' => new PersonalityResource($personalityResponse), // Use resource class
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            // Rollback transaction on model not found
            DB::rollBack();
            return response()->json([
                'message' => 'Personality not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            // Rollback transaction on any other exception
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $reqId)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();

            //get the ids of customer
            $employee = $this->employeeService->findEmlpoyeeById($reqId);

            //get the customer personality id
            $id = $employee->personality_id;

            $personality = $this->personalityService->findPersonalityById($id);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Employee and Personality retrieved successfully',
                'employee' => $employee, // Use resource class
                'personality' => $personality, // Use resource class
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            // Rollback transaction on model not found
            DB::rollBack();
            return response()->json([
                'message' => 'Employee not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            // Rollback transaction on any other exception
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $reqId, PersonalityController $personalityController, EmployeeController $employeeController)
    {
        // Summons the storeRequest from both controllers
        $employeeStoreRequest = new EmployeeUpdateRequest();
        $personalityStoreRequest = new PersonalityUpdateRequest();

        // Access the customer and personality data
        $employeeData = $request->input('employee');
        $personalityData = $request->input('personality');

        // Merge data for validation
        $datas = array_merge($employeeData, $personalityData);
        $rules = array_merge($employeeStoreRequest->rules(), $personalityStoreRequest->rules());

        // Validate data
        $validate = Validator::make($datas, $rules);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation error!',
                'data' => $datas,
                'error' => $validate->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            //first update the customer
            $employeeResponse = $employeeController->update(new Request($employeeData), $reqId);

            $id = $employeeData['personality_id'];

            $personalityResponse = $personalityController->update(new Request($personalityData), $id);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Employee and Personality saved successfully',
                'customer' => new EmployeeResource($employeeResponse), // Use resource class
                'personality' => new PersonalityResource($personalityResponse), // Use resource class
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            // Rollback transaction on model not found
            DB::rollBack();
            return response()->json([
                'message' => 'Employee not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            // Rollback transaction on any other exception
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $reqId)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();

            //get the ids of customer
            $employee = $this->employeeService->findEmlpoyeeById($reqId);

            //get the customer personality id
            $id = $employee->personality_id;

            //delete both customer and personality
            $this->employeeService->deleteEmployee($reqId);
            $this->personalityService->deletePersonality($id);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Employee and Personality delete successfully',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            // Rollback transaction on model not found
            DB::rollBack();
            return response()->json([
                'message' => 'Employee not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            // Rollback transaction on any other exception
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
