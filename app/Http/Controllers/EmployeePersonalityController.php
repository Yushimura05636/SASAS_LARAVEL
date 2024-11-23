<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Requests\PersonalityStoreRequest;
use App\Http\Requests\PersonalityUpdateRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\PersonalityResource;
use App\Http\Resources\UserResource;
use App\Interface\Service\CustomerServiceInterface;
use App\Interface\Service\EmployeeServiceInterface;
use App\Interface\Service\PersonalityServiceInterface;
use App\Models\Customer;
use App\Models\Document_Map;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use App\Models\Employee;
use App\Models\Name_Type;
use App\Models\Personality;
use App\Models\User_Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeePersonalityController extends Controller
{
    private $employeeService;
    private $personalityService;

    public function __construct(EmployeeServiceInterface $employeeService, PersonalityServiceInterface $personalityServiceInterface, CustomerServiceInterface $customers)
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
    // $employees = $this->employeeService->findNoUserEmployees(); // Assuming findCustomers returns employees
    $personalities = $this->personalityService->findPersonality();

    // Query for employees without associated user accounts
$employeesWithoutUsers = Employee::leftJoin('user_account as user', 'employee.id', '=', 'user.employee_id')
->whereNull('user.employee_id')
->select('employee.id', 'employee.personality_id', DB::raw("'employee' as type"));

// Query for customers without associated user accounts
$customersWithoutUsers = Customer::leftJoin('user_account as user', 'customer.id', '=', 'user.customer_id')
->whereNull('user.customer_id')
->select('customer.id', 'customer.personality_id', DB::raw("'customer' as type"));

// Combine both queries using union
$results = $employeesWithoutUsers->union($customersWithoutUsers)->get();

    // //return response()->json(['message' => [$employees,$personalities]]);

    // // Create an associative array (lookup) for personalities using personality_id as key
    // $personalityMap = [];
    // foreach ($personalities as $personality) {
    //     $personalityMap[$personality->id] = $personality;
    // }

    // // Loop through employees and pair them with their respective personality
    // $employeesWithPersonality = [];
    // foreach ($employees as $employee) {
    //     $personalityId = $employee->personality_id;
    //     // Find the corresponding personality using the personality_id

    //     $personality = $personalityMap[$personalityId] ?? null;

    //     // Pair the employee with their personality
    //     $employeesWithPersonality[] = [
    //         'employee' => $employee,
    //         'personality' => $personality
    //     ];

    // }

    // // Return the paired employees and personalities
    // return [
    //     'data' => $employeesWithPersonality
    // ];

    // Create an associative array for personalities using personality_id as the key
$personalityMap = [];
foreach ($personalities as $personality) {
    $personalityMap[$personality->id] = $personality;
}

// Pair entities (employees/customers) with their respective personalities
$entitiesWithPersonality = [];
foreach ($results as $entity) {
    $personalityId = $entity->personality_id;

    // Find the corresponding personality
    $personality = $personalityMap[$personalityId] ?? null;

    // Pair the entity with its personality
    $entitiesWithPersonality[] = [
        'entity' => $entity,
        'personality' => $personality
    ];
}

// Return the combined result
return [
    'data' => $entitiesWithPersonality
];
}

public function store(
    Request $request, 
    EmployeeController $employeeController, 
    PersonalityController $personalityController,
    UserController $userAccount
)
{
    // Summons the storeRequest from both controllers
    $employeeStoreRequest = new EmployeeStoreRequest();
    $personalityStoreRequest = new PersonalityStoreRequest();

    // Access the employee and personality data
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

        // Then put the ID to personality_id in employee
        $employeeData['personality_id'] = $id;
        $employeeResponse = $employeeController->store(new Request($employeeData));

        // Extract `employee_id` from the stored employee response
        $employee_id = $employeeResponse->id ?? null; // Adjust based on the returned structure

        if (!$employee_id) {
            throw new \Exception("Failed to retrieve employee ID.");
        }

        // Prepare user payload
        $userPayload = [
            'customer_id' => null,
            'employee_id' => $employee_id, // Use the extracted ID
            'email' => $personalityData['email_address'],
            'last_name' => $personalityData['family_name'],
            'first_name' => $personalityData['first_name'],
            'middle_name' => $personalityData['middle_name'],
            'phone_number' => $personalityData['cellphone_no'],
            'password' => $request->input('password'), 
            'status_id' => 1,// Ensure password is provided in the request
            'notes' => $request->input('notes'),
        ];

        $userAccountResponse = User_Account::create($userPayload);

        //create dashboard permission
        $document_maps = Document_Map::where('description', 'like', '%Dashboard_Employee%')->first();
        $document_permission_map = Document_Permission_Map::where('description', 'like', '%View%')->first();

        //create the map
        $document_permission = Document_Permission::create([
            'user_id' => $userAccountResponse['id'],
            'document_map_code' => $document_maps->id,
            'document_permission' => $document_permission_map->id,
            'datetime_granted' => now()
        ]);

        if($request->input('notes') == 'employee.collector')
        {
            //create dashboard permission
            $document_maps = Document_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%PAYMENTS%')
                      ->orWhere('description', 'like', '%PAYMENT_SCHEDULES%');
            })->get();
            $document_permission_map = Document_Permission_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%View%')
                      ->orWhere('description', 'like', '%Create%')
                      ->orWhere('description', 'like', '%Update%');
            })->get();
            

            foreach($document_maps as $map)
            {
                if(isset($map) && !is_null($map))
                {
                    foreach($document_permission_map as $permission)
                    {
                        if(isset($permission) && !is_null($permission))
                        {
                            //create the map
                            $document_permission = Document_Permission::create([
                                'user_id' => $userAccountResponse['id'],
                                'document_map_code' => $map->id,
                                'document_permission' => $permission->id,
                                'datetime_granted' => now()
                            ]);
                        }
                    }
                }
            }
        }

        if($request->input('notes') == 'employee.membership.approval.manager')
        {
            //create dashboard permission
            $document_maps = Document_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%CUSTOMER%');
            })->get();
            $document_permission_map = Document_Permission_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%View%')
                      ->orWhere('description', 'like', '%Update%')
                      ->orWhere('description', 'like', '%Approve%')
                      ->orWhere('description', 'like', '%Reject%');
            })->get();
            

            foreach($document_maps as $map)
            {
                if(isset($map) && !is_null($map))
                {
                    foreach($document_permission_map as $permission)
                    {
                        if(isset($permission) && !is_null($permission))
                        {
                            //create the map
                            $document_permission = Document_Permission::create([
                                'user_id' => $userAccountResponse['id'],
                                'document_map_code' => $map->id,
                                'document_permission' => $permission->id,
                                'datetime_granted' => now()
                            ]);
                        }
                    }
                }
            }
        }

        if($request->input('notes') == 'employee.supervisor')
        {
            //create dashboard permission
            $document_maps = Document_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%PAYMENT%');
            })->get();
            $document_permission_map = Document_Permission_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%View%')
                      ->orWhere('description', 'like', '%Update%')
                      ->orWhere('description', 'like', '%Approve%')
                      ->orWhere('description', 'like', '%Reject%');
            })->get();
            

            foreach($document_maps as $map)
            {
                if(isset($map) && !is_null($map))
                {
                    foreach($document_permission_map as $permission)
                    {
                        if(isset($permission) && !is_null($permission))
                        {
                            //create the map
                            $document_permission = Document_Permission::create([
                                'user_id' => $userAccountResponse['id'],
                                'document_map_code' => $map->id,
                                'document_permission' => $permission->id,
                                'datetime_granted' => now()
                            ]);
                        }
                    }
                }
            }
        }

        if($request->input('notes') == 'employee.loan.approval.manager')
        {
            //create dashboard permission
            $document_maps = Document_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%LOAN_APPLICATION%');
            })->get();
            $document_permission_map = Document_Permission_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%View%')
                      ->orWhere('description', 'like', '%Update%')
                      ->orWhere('description', 'like', '%Approve%')
                      ->orWhere('description', 'like', '%Reject%');
            })->get();
            

            foreach($document_maps as $map)
            {
                if(isset($map) && !is_null($map))
                {
                    foreach($document_permission_map as $permission)
                    {
                        if(isset($permission) && !is_null($permission))
                        {
                            //create the map
                            $document_permission = Document_Permission::create([
                                'user_id' => $userAccountResponse['id'],
                                'document_map_code' => $map->id,
                                'document_permission' => $permission->id,
                                'datetime_granted' => now()
                            ]);
                        }
                    }
                }
            }
        }

        if($request->input('notes') == 'employee.cashier')
        {
            //create dashboard permission
            $document_maps = Document_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%PAYMENTS%');
            })->get();
            $document_permission_map = Document_Permission_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%View%')
                    ->orWhere('description', 'like', '%Create%')
                    ->orWhere('description', 'like', '%Update%')
                    ->orWhere('description', 'like', '%Approve%')
                    ->orWhere('description', 'like', '%Reject%');
            })->get();
            

            foreach($document_maps as $map)
            {
                if(isset($map) && !is_null($map))
                {
                    foreach($document_permission_map as $permission)
                    {
                        if(isset($permission) && !is_null($permission))
                        {
                            //create the map
                            $document_permission = Document_Permission::create([
                                'user_id' => $userAccountResponse['id'],
                                'document_map_code' => $map->id,
                                'document_permission' => $permission->id,
                                'datetime_granted' => now()
                            ]);
                        }
                    }
                }
            }
        }

        if($request->input('notes') == 'employee')
        {
            //create dashboard permission
            $document_maps = Document_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%LOAN_APPLICATION%')
                    ->orWhere('description', 'like', '%CUSTOMER%');
            })->get();
            $document_permission_map = Document_Permission_Map::where(function(Builder $query) {
                $query->where('description', 'like', '%View%')
                    ->orWhere('description', 'like', '%Create%')
                    ->orWhere('description', 'like', '%Update%')
                    ->orWhere('description', 'like', '%Approve%')
                    ->orWhere('description', 'like', '%Reject%');
            })->get();
            
            foreach($document_maps as $map)
            {
                if(isset($map) && !is_null($map))
                {
                    foreach($document_permission_map as $permission)
                    {
                        if($map->description == 'LOAN_APPLICATIONS')
                        {
                            if(isset($permission) && !is_null($permission))
                            {
                                if($permission->description != 'APPROVED'
                                    || $permission->description != 'REJECT'
                                ){
                                    //create the map
                                    $document_permission = Document_Permission::create([
                                        'user_id' => $userAccountResponse['id'],
                                        'document_map_code' => $map->id,
                                        'document_permission' => $permission->id,
                                        'datetime_granted' => now()
                                    ]);
                                }
                            }
                        }

                        if($map->description == 'CUSTOMERS')
                        {
                            if(isset($permission) && !is_null($permission))
                            {
                                if($permission->description != 'APPROVED'
                                    || $permission->description != 'REJECT'
                                ){
                                    //create the map
                                    $document_permission = Document_Permission::create([
                                        'user_id' => $userAccountResponse['id'],
                                        'document_map_code' => $map->id,
                                        'document_permission' => $permission->id,
                                        'datetime_granted' => now()
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        else

        {
            if($request->input('notes') == 'admin')
            {
                //create dashboard permission
                $document_maps = Document_Map::get();
                $document_permission_map = Document_Permission_Map::get();
                
                foreach($document_maps as $map)
                {
                    if(isset($map) && !is_null($map))
                    {
                        foreach($document_permission_map as $permission)
                        {
                            if(isset($permission) && !is_null($permission))
                            {
                                //create the map
                                $document_permission = Document_Permission::create([
                                    'user_id' => $userAccountResponse['id'],
                                    'document_map_code' => $map->id,
                                    'document_permission' => $permission->id,
                                    'datetime_granted' => now()
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Commit the transaction
        DB::commit();
        
        return response()->json([
            'message' => 'Both Employee and Personality saved successfully',
            'success' => true,
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
            'message' => substr($e->getMessage(), 0, 95) . '...',
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

    public function showEmployeeDetails()
    {
        try {
            // Start a database transaction
            DB::beginTransaction();

            $user_id = auth()->user()->id;

            $employee_id = User_Account::where('id', $user_id)->first();

            if($employee_id && !is_null($employee_id))
            {
                $employee_id = $employee_id->id;
            }

            //get the ids of customer
            $employee = $this->employeeService->findEmlpoyeeById($employee_id);

            //get the customer personality id
            $id = $employee->personality_id;

            $personality = $this->personalityService->findPersonalityById($id);

            $personality_name_type_id = $personality->name_type_code;

            $name_type_description = Name_Type::where('id', $personality_name_type_id)->first();

            if($name_type_description && !is_null($name_type_description))
            {
                //get the description of the name type
                $employee['name_type'] = $name_type_description->description;
            }

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

    public function getOnlyCollectorPermissions()
    {
        $document_map = Document_Map::whereIn('description', ['CUSTOMER_GROUPS', 'PAYMENTS', 'PAYMENT_SCHEDULES', 'PAYMENT_LINES', 'CUSTOMERS'])
        ->get() // Get all records
        ->mapWithKeys(function ($item) {
            return [$item->description => $item]; // Map each item with description as key
        });

        $permissions = Document_Permission::join('document_map', 'document_permission.document_map_code', '=', 'document_map.id')
        ->whereIn('document_map.description', ['CUSTOMER_GROUPS', 'PAYMENTS', 'PAYMENT_SCHEDULES', 'PAYMENT_LINES', 'CUSTOMERS'])
        ->distinct('document_permission.user_id') // Add distinct for user_id
        ->select('document_permission.user_id') // Only select user_id
        ->get();

        $user_map = [];
        $index = 0;
        foreach($permissions as $perm)
        {
            if(!is_null($perm))
            {
                $user_id = $perm->user_id;
                $users = User_Account::where('id', $user_id)->first();

                $user_map[$index] = $users;

            }
            $index++;
        }

        return response()->json(['data' => $user_map], Response::HTTP_OK);
    }
}
