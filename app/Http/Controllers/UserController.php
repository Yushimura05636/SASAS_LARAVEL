<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Interface\Service\UserServiceInterface;
use App\Mail\TwoFactorCodeMail;
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
        return $this->userService->createUser($request);

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

        else if($request->method == 'phone')
        {
            $request->validate([
                'phone_number' => 'required',
            ]);
            $request['phone_number'] = '+63' . $request->phone_number;
            $phoneVerification = new PhoneVerificationController($request);
            return $phoneVerification->sendPhoneVerification();
        }
        else if($request->method == 'forgot')
        {
            $emailVerificaiton = new ForgotPasswordController($request);
            return $emailVerificaiton->sendResetLink();
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['email' => 'required']);
        $request->validate(['code' => 'required']);

        // return response()->json([
        //     'message' => 'hello',
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // $request->validate(['code' => 'required', 'email' => 'required']);

        if($request->method == 'email')
        {
            $verifyEmail = new EmailVerificationController($request);
            return $verifyEmail->verifyEmailCode();
        }

        else if($request->method == 'phone')
        {
            $request->validate(['phone_number' => 'required']);
            $verifyPhone = new PhoneVerificationController($request);
            return $verifyPhone->verifyPhoneCode();
        }

        else if($request->method == 'forgot')
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
        'password' => 'required|min:8|confirmed',
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

        // Commit transaction and return success response
        DB::commit();
        return response()->json(['success' => true, 'message' => 'Password updated successfully'], Response::HTTP_OK);

    } catch (\Exception $e) {
        // Rollback in case of any exception
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    }
}
