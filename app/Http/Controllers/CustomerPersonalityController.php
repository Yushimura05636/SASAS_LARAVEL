<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Requests\PersonalityStoreRequest;
use App\Http\Requests\PersonalityUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\PersonalityResource;
use App\Interface\Service\CustomerServiceInterface;
use App\Interface\Service\PersonalityServiceInterface;
use App\Models\Personality;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerPersonalityController extends Controller
{
    private $customerService;
    private $personalityService;

    public function __construct(CustomerServiceInterface $customerService, PersonalityServiceInterface $personalityServiceInterface)
    {
        $this->customerService = $customerService;
        $this->personalityService = $personalityServiceInterface;
    }

    public function index()
{
    // Fetch customers and personalities from their respective services
    $customers = $this->customerService->findCustomers();
    $personalities = $this->personalityService->findPersonality();

    // Create an associative array (lookup) for personalities using personality_id as key
    $personalityMap = [];
    foreach ($personalities as $personality) {
        $personalityMap[$personality->id] = $personality;
    }

    // Loop through customers and pair them with their respective personality
    $customersWithPersonality = [];
    foreach ($customers as $customer) {
        $personalityId = $customer->personality_id;
        // Find the corresponding personality using the personality_id
        $personality = $personalityMap[$personalityId] ?? null;

        // Pair the customer with their personality
        $customersWithPersonality[] = [
            'customer' => $customer,
            'personality' => $personality
        ];
    }

    // Return the paired customers and personalities
    return [
        'data' => $customersWithPersonality
    ];
}


    public function store(Request $request, CustomerController $customerController, PersonalityController $personalityController)
    {
        // Summons the storeRequest from both controllers
        $customerStoreRequest = new CustomerStoreRequest();
        $personalityStoreRequest = new PersonalityStoreRequest();

        // Access the customer and personality data
        $customerData = $request->input('customer');
        $personalityData = $request->input('personality');

        // return response()->json([
        //     'message' => 'data',
        //     'data' => $personalityData,
        //     'error' => '',
        // ], Response::HTTP_BAD_REQUEST);

        // Merge data for validation
        $datas = array_merge($customerData, $personalityData);
        $rules = array_merge($customerStoreRequest->rules(), $personalityStoreRequest->rules());

        // Validate data
        $validate = Validator::make($datas, $rules);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation error!',
                'data' => $datas,
                'error' => $validate->errors(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $customerData['personality_id'] = $id;
            $customerResponse = $customerController->store(new Request($customerData));

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Customer and Personality saved successfully',
                'customer' => new CustomerResource($customerResponse), // Use resource class
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
            $customer = $this->customerService->findCustomerById($reqId);

            //get the customer personality id
            $id = $customer->personality_id;

            $personality = $this->personalityService->findPersonalityById($id);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Customer and Personality retrieved successfully',
                'customer' => $customer, // Use resource class
                'personality' => $personality, // Use resource class
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            // Rollback transaction on model not found
            DB::rollBack();
            return response()->json([
                'message' => 'Customer not found.',
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

    public function update(Request $request, int $reqId, PersonalityController $personalityController, CustomerController $customerController)
    {
        // Summons the storeRequest from both controllers
        $customerStoreRequest = new CustomerUpdateRequest();
        $personalityStoreRequest = new PersonalityUpdateRequest();

        // Access the customer and personality data
        $customerData = $request->input('customer');
        $personalityData = $request->input('personality');

        // Merge data for validation
        $datas = array_merge($customerData, $personalityData);
        $rules = array_merge($customerStoreRequest->rules(), $personalityStoreRequest->rules());

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
            $customerResponse = $customerController->update(new Request($customerData), $reqId);

            $id = $customerData['personality_id'];

            $personalityResponse = $personalityController->update(new Request($personalityData), $id);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Customer and Personality saved successfully',
                'customer' => new CustomerResource($customerResponse), // Use resource class
                'personality' => new PersonalityResource($personalityResponse), // Use resource class
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            // Rollback transaction on model not found
            DB::rollBack();
            return response()->json([
                'message' => 'Customer not found.',
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
            $customer = $this->customerService->findCustomerById($reqId);

            //get the customer personality id
            $id = $customer->personality_id;

            //delete both customer and personality
            $this->customerService->deleteCustomer($reqId);
            $this->personalityService->deletePersonality($id);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Both Customer and Personality delete successfully',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            // Rollback transaction on model not found
            DB::rollBack();
            return response()->json([
                'message' => 'Customer not found.',
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
