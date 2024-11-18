<?php

namespace App\Http\Controllers;

use App\Mail\PorfileChangePasswordMail;
use App\Mail\TwoFactorCodeMail;
use App\Mail\VerificationCodeMail;
use App\Models\Customer;
use App\Models\Personality;
use App\Models\User_Account;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function sendEmailVerification()
    {
        $user = User_Account::where('email', $this->request->email)->first();
        
        // Generate a random 6-digit code
        $code = random_int(100000, 999999);

        // Save the code and expiration
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        // return response()->json([
        //     'message' => $user,
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // Send the code via Brevo email
        Mail::to($this->request->email)->send(new TwoFactorCodeMail($code));

        return response()->json(['success' => true, 'message' => '2FA code sent']);
    }

    public function sendEmailVerificationUsingMemory()
    {
        $email = $this->request->email;

        // Check if the email is already registered in the Personality table
        $emailExistsInPersonality = Personality::where('email_address', $email)->exists();
        
        if ($emailExistsInPersonality) {
            return response()->json(['success' => false, 'message' => 'Email already registered!'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the email is already registered in the User_Account table
        $emailExistsInUserAccount = User_Account::where('email', $email)->exists();

        if ($emailExistsInUserAccount) {
            return response()->json(['success' => false, 'message' => 'Email already registered!'], Response::HTTP_BAD_REQUEST);
        }

        //return response()->json(['message' => $email ], Response::HTTP_INTERNAL_SERVER_ERROR);
        
        // Generate a random 6-digit code
        $code = random_int(100000, 999999);

        Cache::put('verify_'.$email, $code, 600); // Store for 10 minutes

        Mail::to($this->request->email)->send(new VerificationCodeMail($code));

        return response()->json(['success' => true, 'message' => '2FA code sent']);
    }

    public function sendProfileEmailVerificationUsingMemory()
    {
        $email = $this->request->email;

        // Check if the email is already registered in the Personality table
        $emailExistsInPersonality = Personality::where('email_address', $email)->exists();
        
        if (!$emailExistsInPersonality) {
            return response()->json(['success' => false, 'message' => 'Email does not recognized!'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the email is already registered in the User_Account table
        $emailExistsInUserAccount = User_Account::where('email', $email)->exists();

        if (!$emailExistsInUserAccount) {
            return response()->json(['success' => false, 'message' => 'Email does not recognized!'], Response::HTTP_BAD_REQUEST);
        }
        
        //return response()->json(['message' => $email ], Response::HTTP_INTERNAL_SERVER_ERROR);
        
        // Generate a random 6-digit code
        $code = random_int(100000, 999999);

        Cache::put('verify_'.$email, $code, 600); // Store for 10 minutes

        Mail::to($this->request->email)->send(new PorfileChangePasswordMail($code));

        return response()->json(['success' => true, 'message' => 'Verification code sent']);
    }

    public function verifyEmailCodeUsingMemory()
    {
        $email = $this->request->email;
        $code = $this->request->code;

        $storedCode = Cache::get('verify_'.$email);

        if ($storedCode > 0 && $storedCode == $code) {
            Cache::forget('verify_'.$email); // Remove the code after successful verification
            return response()->json(['success' => true, 'message' => 'Successfully verified!'], Response::HTTP_OK);
        }
        
        return response()->json(['success' => false, 'message' => 'Invalid or expired code'], Response::HTTP_BAD_REQUEST);
    }

    public function verifyEmailCode()
    {
        $user = User_Account::where('email', $this->request->email)->first();

            $token = $user->createToken('auth-token')->plainTextToken;

            $data = (object) [
                'token' => $token,
            ];
            // Check if code matches and is valid
            if ($user->two_factor_code === $this->request->code && $user->two_factor_expires_at->isFuture()) {
                $user->two_factor_code = null; // Clear the code after successful verification
                $user->save();
                return response()->json(['success' => true, 'data' => $data]);
            }
            return response()->json(['success' => false, 'message' => 'Invalid or expired code'], 422);
    }


    public function sendEmailClientVerification()
    {   
        $user = User_Account::where('email', $this->request->email)->first();
        // Generate a random 6-digit code
        $code = random_int(100000, 999999);

        // Save the code and expiration
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        // return response()->json([
        //     'message' => $user,
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // Send the code via Brevo email
        Mail::to($this->request->email)->send(new TwoFactorCodeMail($code));

        return response()->json(['success' => true, 'message' => '2FA code sent']);
    }



    public function verifyClientEmailCode()
    {
        $user = User_Account::where('email', $this->request->email)->first();

        $token = $user->createToken('auth-token')->plainTextToken;

        $data = (object) [
            'token' => $token,
        ];
        // Check if code matches and is valid
        if ($user->two_factor_code === $this->request->code && $user->two_factor_expires_at->isFuture()) {
            $user->two_factor_code = null; // Clear the code after successful verification
            $user->save();
            return response()->json(['success' => true, 'data' => $data]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid or expired code'], 422);
        
    }
}
