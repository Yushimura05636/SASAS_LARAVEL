<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Requests\PersonalityStoreRequest;
use App\Http\Requests\PersonalityUpdateRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\PersonalityResource;
use App\Http\Resources\UserResource;
use App\Interface\Service\CustomerServiceInterface;
use App\Interface\Service\PersonalityServiceInterface;
use App\Models\Credit_Status;
use App\Models\Customer;
use App\Models\Customer_Requirements;
use App\Models\Document_Permission_Map;
use App\Models\Document_Status_Code;
use App\Models\Loan_Application;
use App\Models\Payment_Schedule;
use App\Models\Personality;
use App\Models\Personality_Status_Map;
use App\Models\User_Account;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        //find the status code description
        $personalityStatusDescription = Personality_Status_Map::where('id', $personality->personality_status_code)->first()->description;

        $personality['personality_status_description'] = $personalityStatusDescription;

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


    public function store(Request $request, PaymentScheduleController $paymentScheduleController, LoanApplicationFeeController $loanApplicationFeeController, CustomerRequirementController $customerRequirementController, CustomerController $customerController, PersonalityController $personalityController)
    {
        // Summons the storeRequest from both controllers
        $customerStoreRequest = new CustomerStoreRequest();
        $personalityStoreRequest = new PersonalityStoreRequest();

        // Access the customer and personality data
        $customerData = $request->input('customer');
        $personalityData = $request->input('personality');
        $requirementDatas = $request->input('requirements');
        $customerFees = $request->input('fees');

        //get the personality status code
        $personalityStatusId = Personality_Status_Map::where('description', 'Pending')->first()->id;

        //set the personality status code
        $personalityData['personality_status_code'] = $personalityStatusId;

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

            $customer_id = Customer::where('passbook_no', $customerData['passbook_no'])->first()->id;

            // return response()->json([
            //     'message' => $customer_id,
            // ], Response::HTTP_BAD_REQUEST);

//diria na part<<<<
            // Create customer_requirements
            for ($i = 0; $i < count($requirementDatas); $i++) {
                $requireData = $requirementDatas[$i];

                $payload = [
                    'customer_id' => $customer_id,
                    'requirement_id' => $requireData['id'],
                    'expiry_date' => $requireData['expiry_date'] ?? null,
                ];

                $payload = new Request($payload);

                $customerRequirementController->store($payload);

                // // Return the current requirement as part of the response for tracing
                // return response()->json([
                //     'message' => $requireData['id'],
                // ], Response::HTTP_BAD_REQUEST);
            }

            //throw new \Exception('stop');
//>>>>

            //create membership payment
            foreach($customerFees as $fee)
            {
                if(!is_null($fee))
                {
                    $payload = [
                        'customer_id' => $customer_id,
                        'loan_released_id' => null,
                        'datetime_due' => now(),
                        'amount_due' => $fee['amount'],
                        'amount_interest' => 0,
                        'amount_paid' => 0,
                        'payment_status_code' => 'UNPAID',
                        'remarks' => null,
                    ];

                    $payload = new Request($payload);

                    // $success = $loanApplicationFeeController->store($payload);

                    //create a schedules for payments
                    $success = $paymentScheduleController->store($payload);

                    if(!$success || is_null($success))
                    {
                        throw new \Exception('Error payment schedule');
                    }
                }
            }

            // throw new \Exception('error');


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


    public function indexApproveActive()
    {
        // Get the personality status code for "Approved"
        $personalityId = Personality_Status_Map::where('description', 'like', '%Approved%')->first()->id;
        $creditId = Credit_Status::where('description', 'like', '%Active%')->first()->id;
        $customers = Customer::get();

        $customerData = [];

        // Loop through each customer
        foreach ($customers as $customer) {
            // Find the related personality with the "Approved" status
            $customerPersonality = Personality::where('id', $customer->personality_id)
                ->where('personality_status_code', $personalityId)
                ->where('credit_status_id', $creditId)
                ->first();

            // If an approved personality is found, add both customer and personality to the result array
            if ($customerPersonality) {
                $customerData[] = [
                    'customer' => $customer,  // Include customer data
                    'personality' => $customerPersonality,  // Include personality data
                ];
            }
        }

        return [
            'data' => $customerData,
        ];

    }

    public function indexActiveWithPayment()
    {
        // Get the personality status code for "Approved"
        //$personalityId = Personality_Status_Map::where('description', 'like', '%Approved%')->first()->id;
        $creditId = Credit_Status::where('description', 'like', '%Active%')->first()->id;
        $customers = Customer::get();

        $customerData = [];

        // Loop through each customer
        foreach ($customers as $customer) {
            // Find payment dues
            $paymentdues = Payment_Schedule::where('customer_id', $customer->id)->exists();

            if(!is_null($paymentdues))
            {

                // Find the related personality with the "Approved" status
                $customerPersonality = Personality::where('id', $customer->personality_id)
                    ->where('credit_status_id', $creditId)
                    ->first();

                // If an approved personality is found, add both customer and personality to the result array
                if ($customerPersonality) {
                    $customerData[] = [
                        'customer' => $customer,  // Include customer data
                        'personality' => $customerPersonality,  // Include personality data
                    ];
                }
            }

        }

        return [
            'data' => $customerData,
        ];

    }


    public function indexApproveActivePending()
    {
        // Get the personality status code for "Approved"
        $personalityId = Personality_Status_Map::where('description', 'like', '%Approved%')->first()->id;
        $creditId = Credit_Status::where('description', 'like', '%Active%')->first()->id;
        $customers = Customer::get();

        $documentStatusId = Document_Status_Code::where('description', 'like', '%Pending%')->first()->id;

        //get
        $loans = Loan_Application::where('document_status_code', $documentStatusId)->get();
        //loans->customer_id to get the customer id on loans
        //return response()->json(['message' => $documentStatusId], Response::HTTP_INTERNAL_SERVER_ERROR);


        $loanCustomerIds = $loans->pluck('customer_id')->toArray(); // Get all customer IDs from loans

        $customerData = [];
        // Loop through each customer
        foreach ($customers as $customer) {
            // Check if the customer ID exists in the loan customer IDs
            if (in_array($customer->id, $loanCustomerIds)) {
                // Find the related personality with the "Approved" status
                $customerPersonality = Personality::where('id', $customer->personality_id)
                    ->where('personality_status_code', $personalityId)
                    ->where('credit_status_id', $creditId)
                    ->first();

                // If an approved personality is found, add both customer and personality to the result array
                if ($customerPersonality) {
                    $customerData[] = [
                        'customer' => $customer,  // Include customer data
                        'personality' => $customerPersonality,  // Include personality data
                    ];
                }
            }
        }

        return [
            'data' => $customerData,
        ];

    }

    public function update(Request $request, int $reqId, CustomerRequirementController $customerRequirementController, PersonalityController $personalityController, CustomerController $customerController)
    {
        // Summons the storeRequest from both controllers
        $customerStoreRequest = new CustomerUpdateRequest();
        $personalityStoreRequest = new PersonalityUpdateRequest();

        // Access the customer and personality data
        $customerData = $request->input('customer');
        $personalityData = $request->input('personality');
        $requirementDatas = $request->input('requirements');

        // //get the personality status code
        // $personalityStatusId = Personality_Status_Map::where('description', 'Pending')->first()->id;

        // //set the personality status code
        // $personalityData['personality_status_code'] = $personalityStatusId;

                // Check if personality_status_code is provided; otherwise, set it to "Pending"
        if (isset($personalityData['personality_status_code'])) {
            $personalityStatusId = $personalityData['personality_status_code'];
        } else {
            $personalityStatusId = Personality_Status_Map::where('description', 'Pending')->first()->id;
        }

        // Set the personality status code
        $personalityData['personality_status_code'] = $personalityStatusId;

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

            $customer_id = Customer::where('passbook_no', $customerData['passbook_no'])->first()->id;

            // return response()->json([
            //     'message' => $customer_id,
            // ], Response::HTTP_BAD_REQUEST);

            // Step 1: Get all requirement_ids from the request data
            $requestedRequirementIds = array_column($requirementDatas, 'id');

            // Step 2: Find and delete database records not in the request data
            Customer_Requirements::where('customer_id', $customer_id)
                ->whereNotIn('requirement_id', $requestedRequirementIds)
                ->delete();

            // Step 3: Update or create customer_requirements
            for ($i = 0; $i < count($requirementDatas); $i++) {
                $requireData = $requirementDatas[$i];

                $payload = [
                    'customer_id' => $customer_id,
                    'requirement_id' => $requireData['id'],
                    'expiry_date' => $requireData['expiry_date'],
                ];

                $payload = new Request($payload);

                // Find the record by customer_id and requirement_id, if it exists
                $customerRequirement = Customer_Requirements::where('customer_id', $customer_id)
                                                            ->where('requirement_id', $requireData['id'])
                                                            ->first();

                if ($customerRequirement) {
                    // Update the existing record
                    // return response()->json([
                    //     'message update' => $payload->all(),
                    // ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    $customerRequirementController->update($payload, $customerRequirement->id);
                } else {
                    // return response()->json([
                    //     'message create' => $payload->all(),
                    // ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    // Create a new record if it doesn't exist
                    $customerRequirementController->store($payload);
                }

                // return response()->json([
                //     'message' => $customerRequirement,
                // ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

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

    public function updateApprove(Request $request, int $reqId, CustomerRequirementController $customerRequirementController, PersonalityController $personalityController, CustomerController $customerController)
    {
        // Summons the storeRequest from both controllers
        $customerStoreRequest = new CustomerUpdateRequest();
        $personalityStoreRequest = new PersonalityUpdateRequest();

        // Access the customer and personality data
        $customerData = $request->input('customer');
        $personalityData = $request->input('personality');
        $requirementDatas = $request->input('requirements');

        //get the customer id using passbook no
        $customerId = Customer::where('passbook_no', $customerData['passbook_no'])->first()->id;

        //check if the customer has pending payments
        $paymentPendings = Payment_Schedule::where('customer_id', $customerId)
        ->where('payment_status_code', 'like', '%Unpaid%')
        ->orWhere('payment_status_code', 'PARTIALLY PAID')
        ->first();

        //throw new \Exception($paymentPendings);

        if($paymentPendings && !is_null($paymentPendings))
        {
            throw new \Exception('Approve denied, the customer still have pending payments');
        }

        // //get the personality status code
        // $personalityStatusId = Personality_Status_Map::where('description', 'Pending')->first()->id;

        // //set the personality status code
        // $personalityData['personality_status_code'] = $personalityStatusId;

        // Check if personality_status_code is provided; otherwise, set it to "Pending"
        if (isset($personalityData['personality_status_code'])) {
            $personalityStatusId = $personalityData['personality_status_code'];
        } else {
            $personalityStatusId = Personality_Status_Map::where('description', 'Pending')->first()->id;
        }

        // Set the personality status code
        $personalityData['personality_status_code'] = $personalityStatusId;

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

            $customer_id = Customer::where('passbook_no', $customerData['passbook_no'])->first()->id;

            // return response()->json([
            //     'message' => $customer_id,
            // ], Response::HTTP_BAD_REQUEST);

            // Step 1: Get all requirement_ids from the request data
            $requestedRequirementIds = array_column($requirementDatas, 'id');

            // Step 2: Find and delete database records not in the request data
            Customer_Requirements::where('customer_id', $customer_id)
                ->whereNotIn('requirement_id', $requestedRequirementIds)
                ->delete();

            // Step 3: Update or create customer_requirements
            for ($i = 0; $i < count($requirementDatas); $i++) {
                $requireData = $requirementDatas[$i];

                $payload = [
                    'customer_id' => $customer_id,
                    'requirement_id' => $requireData['id'],
                    'expiry_date' => $requireData['expiry_date'],
                ];

                $payload = new Request($payload);

                // Find the record by customer_id and requirement_id, if it exists
                $customerRequirement = Customer_Requirements::where('customer_id', $customer_id)
                                                            ->where('requirement_id', $requireData['id'])
                                                            ->first();

                if ($customerRequirement) {
                    // Update the existing record
                    // return response()->json([
                    //     'message update' => $payload->all(),
                    // ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    $customerRequirementController->update($payload, $customerRequirement->id);
                } else {
                    // return response()->json([
                    //     'message create' => $payload->all(),
                    // ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    // Create a new record if it doesn't exist
                    $customerRequirementController->store($payload);
                }

                // return response()->json([
                //     'message' => $customerRequirement,
                // ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

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

    public function showGroupApprove(int $id)
    {

        //get first the approve id
        $personalityStatusId = Personality_Status_Map::where('description', 'Approved')->first()->id;

        $customers = Customer::where('group_id', $id)
        ->with('personality')  // Include related personality data
        ->orderBy('personality_id')
        ->get();

        foreach($customers as $customer)
        {
            if(!is_null($customer))
            {
                $canReloan = Payment_Schedule::where('customer_id', $customer->id)
                ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED'])
                ->where('payment_status_code', ['UNPAID', 'PARTIALLY PAID'])
                ->doesntExist();

                //throw new \Exception($canReloan . ' ' . $customer->id);
                if(!$canReloan > 0 || is_null($canReloan))
                {
                    throw new \Exception('The group cannot be able to loan or reloan because there is still member that has dues.');
                }
            }
        }
        
        $customerDatas = [];

        foreach ($customers as $customer) {
            if ($customer['personality']['personality_status_code'] == $personalityStatusId) {
                $customerDatas[] = $customer; // Using array shorthand
            }
        }

        // return response()->json([
        //     'message group' => $customerDatas,
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);

        if(!count($customerDatas) > 0)
        {
            return response()->json([
                'message' => 'there is no customer in this group'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $customerDatas,
        ]);


    }

    public function showGroupApproveActive(int $id)
    {

        //get first the approve id
        $personalityStatusId = Personality_Status_Map::where('description', 'like', '%Approved%')
        ->first()->id;

        $creditStatusId = Credit_Status::where('description', 'like', '%Active%')
        ->first()->id;

        $customers = Customer::where('group_id', $id)
        ->with('personality')  // Include related personality data
        ->orderBy('personality_id')
        ->get();


        $customerDatas = [];

        foreach ($customers as $customer) {
            if ($customer['personality']['personality_status_code'] == $personalityStatusId
            && $customer['personality']['credit_status_id'] == $creditStatusId) {
                $customerDatas[] = $customer; // Using array shorthand
            }
        }

        // return response()->json([
        //     'message group' => $customerDatas,
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);

        if(!count($customerDatas) > 0)
        {
            return response()->json([
                'message' => 'there is no customer in this group'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $customerDatas,
        ]);


    }

    public function showGroupWithData(int $id)
    {
        //get first the approve id
        //$personalityStatusId = Personality_Status_Map::where('description', 'Approved')->first()->id;
        
        //check if the group can reloan
        
        $customers = Customer::where('group_id', $id)
        ->with('personality')  // Include related personality data
        ->orderBy('personality_id')
        ->get();

        foreach($customers as $customer)
        {
            if(!is_null($customer))
            {

                //the right approach here is it should only read the suspended and active
                $canReloan = Payment_Schedule::where('customer_id', $customer->id)
                ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED'])
                ->where('payment_status_code', ['UNPAID', 'PARTIALLY PAID'])
                ->doesntExist();

                //throw new \Exception($canReloan . ' ' . $customer->id);
                if(!$canReloan > 0 || is_null($canReloan))
                {
                    throw new \Exception('The group cannot be able to loan or reloan because there is still member that has dues.');
                }
            }
        }


        // Get the document status code for pending
        $status_code = Document_Status_Code::where('description', 'like', '%Pending%')->first()?->id;

        // Check if there is any pending loan application
        $hasPending = $status_code 
            ? Loan_Application::where('group_id', $id)
                ->where('document_status_code', $status_code)
                ->exists()
            : false;

            //throw new \Exception($hasPending);
        if($hasPending > 0)
        {
            throw new \Exception('this group still has pending loans');
        }


        $customerDatas = [];

        foreach ($customers as $customer) {
            if(!is_null($customer))
            {
                $customerDatas[] = $customer; // Using array shorthand
            }
            // if ($customer['personality']['personality_status_code'] == $personalityStatusId) {
            // }
        }

        // return response()->json([
        //     'message group' => $customerDatas,
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);

        if(!count($customerDatas) > 0)
        {
            return response()->json([
                'message' => 'there is no customer in this group'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $customerDatas,
        ]);
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

    public function storeForRegistration(
        Request $request,
        PaymentScheduleController $paymentScheduleController,
        CustomerController $customerController,
        PersonalityController $personalityController,
        UserController $userAccount
    ) {
        // Summons the storeRequest from both controllers
        $customerStoreRequest = new CustomerStoreRequest();
        $personalityStoreRequest = new PersonalityStoreRequest();
        $userStoreRequest = new UserStoreRequest(); // Already existing validation class for users
    
        // Access the customer and personality data
        $customerData = $request->input('customer');
        $personalityData = $request->input('personality');
        $customerFees = $request->input('fees');
    
        // Get the personality status code
        $personalityStatusId = Personality_Status_Map::where('description', 'Pending')->first()->id;
        $personalityData['personality_status_code'] = $personalityStatusId;
    
        // Ensure datetime_registered is set, default to current date if not provided
        $personalityData['datetime_registered'] = $personalityData['datetime_registered'] ?? now()->toDateTimeString();
    
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
            $requestForPersonality = new Request();
            $requestForPersonality->merge($personalityData);
            $personalityResponse = $personalityController->store($requestForPersonality);
    
            // Attempt to find the personality by first name, family name, and middle name
            $personality = Personality::where('first_name', $personalityData['first_name'])
                ->where('family_name', $personalityData['family_name'])
                ->where('middle_name', $personalityData['middle_name'])
                ->firstOrFail();
    
            // Get the ID of the found personality
            $id = $personality->id;
    
            // Then put the ID to personality_id in customer
            $customerData['personality_id'] = $id;
    
            $requestForCustomer = new Request();
            $requestForCustomer->merge($customerData);
            $customerResponse = $customerController->store($requestForCustomer);
    
            $customer_id = Customer::where('passbook_no', $customerData['passbook_no'])->first()->id;
    
            // Create membership payment
            foreach ($customerFees as $fee) {
                if (!is_null($fee)) {
                    $payload = [
                        'customer_id' => $customer_id,
                        'loan_released_id' => null,
                        'datetime_due' => now(),
                        'amount_due' => $fee['amount'],
                        'amount_interest' => 0,
                        'amount_paid' => 0,
                        'payment_status_code' => 'UNPAID',
                        'remarks' => null,
                    ];
    
                    $requestForPayment = new Request($payload);
                    $success = $paymentScheduleController->store($requestForPayment);
    
                    if (!$success || is_null($success)) {
                        throw new \Exception('Error creating payment schedule');
                    }
                }
            }
    
            // Prepare User_Account payload
            $userPayload = (object)[
                'customer_id' => $customer_id,
                'email' => $personalityData['email_address'],
                'last_name' => $personalityData['family_name'],
                'first_name' => $personalityData['first_name'],
                'middle_name' => $personalityData['middle_name'],
                'phone_number' => $personalityData['cellphone_no'],
                'password' => $request->input('password'), // Ensure password is provided in the request
                'status_id' => $personalityStatusId,
            ];
    
            // Create a new UserStoreRequest and merge the payload data
            $userRequest = new UserStoreRequest();
            $userRequest->merge((array)$userPayload); // Merge the data into the request object
    
            // Call the store method for User_Account
            $userAccountResponse = $userAccount->store($userRequest); // Now passing UserStoreRequest
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'message' => 'Customer, Personality, and User Account saved successfully',
                'customer' => new CustomerResource($customerResponse), // Use resource class
                'personality' => new PersonalityResource($personalityResponse), // Use resource class
                'user_account' => new UserResource($userAccountResponse),
            ], Response::HTTP_OK);
    
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Personality not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
    
}
