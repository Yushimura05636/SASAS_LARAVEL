<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Interface\Service\UserServiceInterface;
use App\Mail\TwoFactorCodeMail;
use App\Models\Customer;
use App\Models\Document_Map;
use App\Models\Document_Status_Code;
use App\Models\Loan_Application;
use App\Models\Payment;
use App\Models\Payment_Schedule;
use App\Models\Personality;
use App\Models\Personality_Status_Map;
use App\Models\User_Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
         // Prepare the payload with the request data
        $userPayload = (object)[
            'employee_id' => $request->input('employee_id'),
            'customer_id' => $request->input('customer_id'),
            'email' => $request->input('email'),
            'last_name' => $request->input('last_name'),
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'phone_number' => $request->input('phone_number'),
            'password' => $request->input('password'),
            'status_id' => $request->input('status_id'),
        ];

        // Pass the formatted payload to the service
        return $this->userService->createUser($userPayload);

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
        //return response()->json(['message', $request->all()], Response::HTTP_INTERNAL_SERVER_ERROR);
        $request->validate(['email' => 'required']);
        $request->validate(['method' => 'required']);

        if($request->method == 'email')
        {
            $emailVerificaiton = new EmailVerificationController($request);
            return $emailVerificaiton->sendEmailVerification();
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

    public function getOnlyCollectorPermissions()
    {
        //show only the user with collector permission
        //collector permission consists of
        //customer_group
        //payments
        //payment_schedules
        //payment_line
        //customer

        $document_map = Document_Map::whereIn('description', ['CUSTOMER_GROUPS', 'PAYMENTS', 'PAYMENT_SCHEDULES', 'PAYMENT_LINES', 'CUSTOMERS'])
        ->get() // Get all records
        ->mapWithKeys(function ($item) {
            return [$item->description => $item]; // Map each item with description as key
        });

        $permissions = DB::table('document_permission')
        ->join('document_map', 'document_permission.document_map_code', '=', 'document_map.id')
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

    $user_details = User_Account::where('id', $user_id)->first();

    // Check if the user is an employee or a customer
    if (is_null($user_details->customer_id)) {
        return response()->json(['data' => $user_details, 'role' => 'EMPLOYEE'], Response::HTTP_OK);
    }

    // Customer role setup
    $role = 'CUSTOMER';
    
    $outstanding_balance = Payment_Schedule::where('customer_id', $user_details->customer_id)
    ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED'])
    ->selectRaw('IFNULL(SUM(amount_due) - SUM(amount_paid), 0) as outstanding_balance')
    ->value('outstanding_balance');

    $total_balances = Payment_Schedule::where('customer_id', $user_details->customer_id)
        ->whereIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'UNPAID'])
        ->sum('amount_due') ?? 0;

    $total_payments = Payment_Schedule::where('customer_id', $user_details->customer_id)
        ->whereIn('payment_status_code', ['PAID', 'PARTIALLY PAID'])
        ->sum('amount_paid') ?? 0;

    $payment_history = Payment::where('customer_id', $user_details->customer_id)->get();
    $loan_history = Loan_Application::where('customer_id', $user_details->customer_id)->get();

    // Get the status codes for Approved and Pending loan applications
    $approveIds = Document_Status_Code::where('description', 'like', '%Approved%')->pluck('id')->toArray();
    $pendingIds = Document_Status_Code::where('description', 'like', '%Pending%')->pluck('id')->toArray();

    $number_of_loans = Loan_Application::where('customer_id', $user_details->customer_id)
        ->whereIn('document_status_code', array_merge($approveIds, $pendingIds))
        ->count();

    // Get the current customer by customer_id from user_details
    $currentCustomer = Customer::where('id', $user_details->customer_id)->first();

    // Initialize the flag to check reloan possibility
    $can_reloan = true;

    $personality_status_code = Personality_Status_Map::where('description', 'like', '%Approved%')
    ->first();

    if ($personality_status_code) {
        $isApproved = Personality::where('id', $currentCustomer->personality_id)
            ->where('personality_status_code', $personality_status_code->id) // or relevant field
            ->exists();

        if(!$isApproved) {
            $can_reloan = false;
        }
    } else {
        // Handle case where no approved status is found (if needed)
        $can_reloan = false;
    }

    // Ensure customer exists before proceeding
    if ($currentCustomer && $can_reloan == true) {
        // Get the group_id of the current customer
        $customerGroupId = $currentCustomer->group_id;

        // Get all other customers in the same group
        $groupCustomers = Customer::where('group_id', $customerGroupId)
            ->with('personality')  // Include related personality data
            ->orderBy('personality_id')
            ->get();

        // Loop through each customer in the group
        foreach ($groupCustomers as $customer) {
            if ($customer) {
                // Check if there are any unpaid or partially paid payment schedules
                $canReloan = Payment_Schedule::where('customer_id', $customer->id)
                    ->whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID', 'FORWARDED'])
                    ->whereIn('payment_status_code', ['UNPAID', 'PARTIALLY PAID'])
                    ->exists(); // returns true if any records found

                // If there are payment issues, set can_reloan to false
                if ($canReloan) {
                    $can_reloan = false;
                    break;  // No need to check further once we know the group can't reloan
                }
            }
        }
    }

    // Prepare the data for the response
    return response()->json([
        'data' => $user_details,
        'role' => $role,
        'outstanding_balance' => $outstanding_balance,
        'total_balances' => $total_balances,
        'total_payments' => $total_payments,
        'payment_history' => $payment_history,
        'loan_history' => $loan_history,
        'number_of_loans' => $number_of_loans,
        'can_reloan' => $can_reloan  // Add the reloan status to the response
    ], Response::HTTP_OK);
}
}
