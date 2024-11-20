<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Interface\Service\UserServiceInterface;
use App\Mail\TwoFactorCodeMail;
use App\Models\Customer;
use App\Models\Document_Map;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use App\Models\Document_Status_Code;
use App\Models\Employee;
use App\Models\Loan_Application;
use App\Models\Payment;
use App\Models\Payment_Schedule;
use App\Models\Personality;
use App\Models\Personality_Status_Map;
use App\Models\User_Account;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;




class UserController extends Controller
{

    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->userService->findUser();
    }


    /**
     * Store a newly created resource in storage.
     */

    public function store(UserStoreRequest $request)
    {
        // Begin a database transaction
        DB::beginTransaction();
    
        try {
            // Prepare the payload with the request data
            $userPayload = (object)[
                'employee_id' => $request->input('employee_id'),
                'customer_id' => $request->input('customer_id'),
                'email' => $request->input('email'),
                'last_name' => $request->input('last_name'),
                'first_name' => $request->input('first_name'),
                'middle_name' => $request->input('middle_name'),
                'phone_number' => $request->input('phone_number'),
                'password' => $request->input('password'),  // Hash the password for security
                'status_id' => $request->input('status_id'),
            ];
    
            // Pass the formatted payload to the service to create a user
            $userAccountResponse = $this->userService->createUser($userPayload);
    
            // Check if employee_id is null or less than or equal to 0 (customer creation logic)
            if (is_null($request->input('employee_id')) || $request->input('employee_id') <= 0) {
                DB::commit();  // Commit transaction for customer creation
                return response()->json(['message' => 'Successfully created customer!'], Response::HTTP_OK);
            }
    
            // Get the document map for employee dashboard permissions
            $document_map = Document_Map::where('description', 'like', '%DASHBOARD_EMPLOYEES%')->first();
            $document_map = $document_map ? $document_map->id : null;
    
            // Get the document permission map
            $document_map_permission = Document_Permission_Map::where('description', 'like', '%View%')->first();
            $document_map_permission = $document_map_permission ? $document_map_permission->id : null;
    
            // Check if both document map and permission are valid
            if ($document_map && $document_map_permission) {
                $employee_id = $request->input('employee_id');
                // Find the user by employee_id
                $user = User_Account::where('employee_id', $employee_id)->first();
    
                if ($user) {
                    $user_id = $user->id;
    
                    // Create a document permission entry for the user
                    Document_Permission::create([
                        'user_id' => $user_id,
                        'document_map_code' => $document_map,
                        'document_permission' => $document_map_permission,
                        'datetime_granted' => now(),
                    ]);
                }
            }
    
            // Commit the transaction after successful creation
            DB::commit();
    
            // Return success response with user ID (if available) or relevant data
            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'User and permissions created successfully!',
                'user_account_id' => $userAccountResponse->id ?? null  // Ensure this is not null, adjust based on your service response
            ], Response::HTTP_OK);
    
        } catch (\Exception $e) {
            // Rollback the transaction if any exception occurs
            DB::rollback();
    
            // Log the error for debugging
            Log::error("Error while creating user account: " . $e->getMessage());
    
            // Return error response
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Transaction failed. Please try again later.',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    




    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->userService->findUserById($id);

    }

    public function showOwnDetails()
    {
        $userId = auth()->user()->id;

        return $this->userService->findUserById($userId);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        return $this->userService->updateUser($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->userService->deleteUser($id);

    }

    public function getUserLogged()
    {
        return $this->userService->getUserLogin();

    }

    public function sent()
    {
        return response()->json([
            'message' => 'hello',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function sendCode(Request $request)
    {
        
        $request->validate(['email' => 'required']);
        $request->validate(['method' => 'required']);

        if($request->method == 'email')
        {
            $emailVerificaiton = new EmailVerificationController($request);
            return $emailVerificaiton->sendEmailVerification();
        }

        if($request->method == 'email.customer')
        {
            $emailVerificaiton = new EmailVerificationController($request);
            return $emailVerificaiton->sendEmailVerificationUsingMemory();
        }

        if($request->method == 'email.customer.profile')
        {
            $emailVerificaiton = new EmailVerificationController($request);
            return $emailVerificaiton->sendProfileEmailVerificationUsingMemory();
        }

        if($request->method == 'phone')
        {
            $request->validate([
                'phone_number' => 'required',
            ]);
            $request['phone_number'] = '+63' . $request->phone_number;
            $phoneVerification = new PhoneVerificationController($request);
            return $phoneVerification->sendPhoneVerification();
        }

        if($request->method == 'forgot')
        {
            $emailVerificaiton = new ForgotPasswordController($request);
            return $emailVerificaiton->sendResetLink();
        }
    }

    public function verifyCode(Request $request)
    {
        //throw new \Exception('stop');

        $request->validate(['email' => 'required']);
        // return response()->json([
        //     'message' => 'hello',
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // $request->validate(['code' => 'required', 'email' => 'required']);

        

        if($request->method == 'email')
        {
            $request->validate(['code' => 'required']);
            $verifyEmail = new EmailVerificationController($request);
            return $verifyEmail->verifyEmailCode();
        }


        if($request->method == 'email.customer')
        {
            $request->validate(['code' => 'required']);
            $verifyEmail = new EmailVerificationController($request);
            return $verifyEmail->verifyEmailCodeUsingMemory();
        }

        if($request->method == 'email.customer.profile')
        {
            $request->validate(['code' => 'required']);
            $verifyEmail = new EmailVerificationController($request);
            return $verifyEmail->verifyEmailCodeUsingMemory();
        }

        if($request->method == 'phone')
        {
            $request->validate(['code' => 'required']);
            $request->validate(['phone_number' => 'required']);
            $verifyPhone = new PhoneVerificationController($request);
            return $verifyPhone->verifyPhoneCode();
        }

        if($request->method == 'forgot')
        {
            $request->validate(['token' => 'required']);
            $verifyEmailAndToken = new ForgotPasswordController($request);
            return $verifyEmailAndToken->verify();
        }
    }

    public function changePassword(Request $request)
    {
    // Validate request data
    $validatedData = $request->validate([
        'email' => 'required|email',
        'token' => 'required',
        'password' => 'required',
    ]);

    // Start the transaction
    DB::beginTransaction();

    try {
        // Check for matching token and email in the password reset table
        $userToken = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->where('email', $request->email)
            ->first();

        if (!$userToken) {
            // Token and email don't match
            return response()->json(['message' => 'Email or token does not match!'], Response::HTTP_BAD_REQUEST);
        }

        // Find the user in the User_Account table
        $user = User_Account::where('email', $userToken->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Update the password
        $newPasswordHash = Hash::make($request->password);
        $user->password = $newPasswordHash;
        $user->save();

        // Check if the password was successfully changed
        if (!Hash::check($request->password, $user->password)) {
            // Rollback if the password was not successfully updated
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Password update failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $role = 'CUSTOMER';
        if(is_null($user->customer_id))
        {
            $role = 'EMPLOYEE';
        }

        // Commit transaction and return success response
        DB::commit();

        //clear the data
        DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

        return response()->json(['success' => true, 'message' => 'Password updated successfully', 'role' => $role], Response::HTTP_OK);

    } catch (\Exception $e) {
        // Rollback in case of any exception
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    }

    public function checkEmail(Request $request) {
        //return response()->json(['success' => false, 'message' => $request->all()], Response::HTTP_INTERNAL_SERVER_ERROR);
        $request->validate(['email' => 'required']);
        $user = User_Account::where('email', $request->email)->first();

        if(is_null($user))
        {
            return response()->json(['success' => false, 'message' => 'Email does not exists!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['success' => true, 'message' => 'Email exists!'], Response::HTTP_OK);
    }

    public function showUserDetails()
    {
        $user_id = auth()->user()->id;

        $user_details = User_Account::where('id', $user_id)->first();

        $role = null;
        if(is_null($user_details->customer_id))
        {
            $role = 'EMPLOYEE';   
        }
        else
        {
            $role = 'CUSTOMER';
        }

        return response()->json(['data' => $user_details, 'role' => $role], Response::HTTP_OK);
    }

public function showUserLoanDetails()
{
    $user_id = auth()->user()->id;

    try {
        // Start a database transaction
        $responseData = DB::transaction(function () use ($user_id) {
            $user_details = User_Account::where('id', $user_id)->first();

            if (!$user_details) {
                throw new \Exception("User details not found");
            }

            // Check if the user is an employee
            if (is_null($user_details->customer_id)) {
                return [
                    'data' => $user_details,
                    'role' => 'EMPLOYEE',
                ];
            }

            $role = 'CUSTOMER';

            // Fetch outstanding balance
            $outstanding_balance = Payment_Schedule::where('customer_id', $user_details->customer_id)
                ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED'])
                ->selectRaw('IFNULL(SUM(amount_due) - SUM(amount_paid), 0) as outstanding_balance')
                ->value('outstanding_balance');

            // Fetch total balances and payments
            $total_balances = Payment_Schedule::where('customer_id', $user_details->customer_id)
                ->whereIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'UNPAID'])
                ->sum('amount_due') ?? 0;

            $total_payments = Payment_Schedule::where('customer_id', $user_details->customer_id)
                ->whereIn('payment_status_code', ['PAID', 'PARTIALLY PAID'])
                ->sum('amount_paid') ?? 0;

            // Fetch payment and loan history
            $payment_history = Payment::where('customer_id', $user_details->customer_id)->get();
            $loan_history = Loan_Application::where('customer_id', $user_details->customer_id)->get();

            // Populate document status descriptions in payment history
            foreach ($payment_history as $payment) {
                if ($payment) {
                    $document_status = Document_Status_Code::where('id', $payment['document_status_code'])->first();
                    $payment['document_status_description'] = strtoupper($document_status->description ?? '');
                }
            }

            // Get the status codes for Approved and Pending loans
            $approveIds = Document_Status_Code::where('description', 'like', '%Approved%')->pluck('id')->toArray();
            $pendingIds = Document_Status_Code::where('description', 'like', '%Pending%')->pluck('id')->toArray();

            $number_of_loans = Loan_Application::where('customer_id', $user_details->customer_id)
                ->whereIn('document_status_code', array_merge($approveIds, $pendingIds))
                ->count();

            // Check reloan eligibility
            $can_reloan = $this->checkReloanEligibility($user_details);

            return [
                'data' => $user_details,
                'role' => $role,
                'outstanding_balance' => $outstanding_balance,
                'total_balances' => $total_balances,
                'total_payments' => $total_payments,
                'payment_history' => $payment_history,
                'loan_history' => $loan_history,
                'number_of_loans' => $number_of_loans,
                'can_reloan' => $can_reloan,
            ];
        });

        return response()->json($responseData, Response::HTTP_OK);

    } catch (\Throwable $e) {
        // Rollback is automatically handled by DB::transaction if an exception occurs
        return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

/**
 * Check if the customer is eligible for reloan.
 */
private function checkReloanEligibility($user_details)
{
    $currentCustomer = Customer::where('id', $user_details->customer_id)->first();
    if (!$currentCustomer) {
        return false;
    }

    $personality_status_code = Personality_Status_Map::where('description', 'like', '%Approved%')->first();
    if (!$personality_status_code) {
        return false;
    }

    $isApproved = Personality::where('id', $currentCustomer->personality_id)
        ->where('personality_status_code', $personality_status_code->id)
        ->exists();

    if (!$isApproved) {
        return false;
    }

    // Check group reloan eligibility
    $groupCustomers = Customer::where('group_id', $currentCustomer->group_id)
        ->with('personality')
        ->orderBy('personality_id')
        ->get();

    foreach ($groupCustomers as $customer) {
        if (Payment_Schedule::where('customer_id', $customer->id)
            ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'FORWARDED'])
            ->whereIn('payment_status_code', ['UNPAID', 'PARTIALLY PAID'])
            ->exists()) {
            return false;
        }
    }

    return true;
}

public function profile()
{
    DB::beginTransaction();  // Start the transaction

    try {

        $user_id = auth()->user()->id;

        // Get the full name of the user
        $user = User_Account::findOrFail($user_id);

        $user_data = null;

        if(isset($user) && !is_null($user))
        {
            $user_data = [
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'email' => $user->email,
            ];
        }

        // Get the phone number of the user and address
        $employee_id = $user->employee_id;

        if(is_null($employee_id) || $employee_id <= 0)
        {
            $customer_id = $user->customer_id;

            $customer = Customer::findOrFail($customer_id);

            $personality = Personality::findOrFail($customer->personality_id);

            if(isset($personality) && !is_null($personality))
            {
                // Get the address
                $user_data['house_street'] = $personality->house_street;
                $user_data['purok_zone'] = $personality->purok_zone;
                $user_data['postal_code'] = $personality->postal_code;
                $user_data['cellphone_no'] = $personality->cellphone_no;

                DB::commit();  // Commit the transaction if everything is successful

                return response()->json(['success' => true, 'message' => 'Successfully retrieved customer profile', 'data' => $user_data], Response::HTTP_OK);
            }
        }

        if(!is_null($employee_id) && $employee_id > 0)
        {
            $employee = Employee::findOrFail($employee_id);

            $personality = Personality::findOrFail($employee->personality_id);

            if(isset($personality) && !is_null($personality))
            {
                // Get the address
                $user_data['house_street'] = $personality->house_street;
                $user_data['purok_zone'] = $personality->purok_zone;
                $user_data['postal_code'] = $personality->postal_code;
                $user_data['cellphone_no'] = $personality->cellphone_no;

                DB::commit();  // Commit the transaction if everything is successful

                return response()->json(['success' => true, 'message' => 'Successfully retrieved employee profile', 'data' => $user_data], Response::HTTP_OK);
            }
        }

        // If no personality data found, throw an exception
        throw new \Exception('Personality data not found');

    } catch (\Exception $e) {
        DB::rollback();  // Rollback the transaction if any exception occurs

        return response()->json(['success' => false, 'message' => 'Failed to retrieve profile. Please try again.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function profileResetPassword(Request $request)
{
    // Validate incoming request
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $email = $validated['email'];
    $password = $validated['password'];

    // Start database transaction
    DB::beginTransaction();

    try {
        // Check if email exists in User_Account table
        $userAccountExists = DB::table('user_account')->where('email', $email)->exists();

        if (!$userAccountExists) {
            return response()->json(['error' => 'Email does not exist in User_Account.'], 404);
        }

        // Check if email exists in Personality table
        $personalityExists = DB::table('personality')->where('email_address', $email)->exists();

        if (!$personalityExists) {
            return response()->json(['error' => 'Email does not exist in Personality.'], 404);
        }

        // Reset password (example: update the User_Account table)
        DB::table('user_account')
            ->where('email', $email)
            ->update(['password' => Hash::make($password)]);

        // Commit transaction
        DB::commit();

        return response()->json(['success' => true, 'message' => 'Password reset successfully.'], 200);
    } catch (\Exception $e) {
        // Rollback transaction in case of error
        DB::rollBack();

        return response()->json(['error' => 'An error occurred during password reset.', 'details' => $e->getMessage()], 500);
    }
}

public function getUserwithEmp(){

    // Retrieve users where employee_id is not null and customer_id is null
    $users = User_Account::whereNotNull('employee_id')
    ->whereNull('customer_id')
    ->get();

return $users;
}



}
