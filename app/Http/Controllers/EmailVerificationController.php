<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\Customer;
use App\Models\Personality;
use App\Models\User_Account;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
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
            Log::info('Received email_address:', ['email' => $this->request->email_address]); // Log the email address
            $user = Personality::where('email_address', $this->request->email_address)->first();
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
            Mail::to($this->request->email_address)->send(new TwoFactorCodeMail($code));

            return response()->json(['success' => true, 'message' => '2FA code sent']);
    }



    public function verifyClientEmailCode()
    {
        Log::info('Received email_address:', ['email' => $this->request->email_address]); // Log the email address
    
        // Retrieve the user by email
        $user = Personality::where('email_address', $this->request->email_address)->first();
        
        // Generate a token (assuming createToken method is available on your User model)
        $token = $user->createToken('auth-token')->plainTextToken;
        
        // Ensure two_factor_expires_at is a Carbon instance
        $expiresAt = Carbon::parse($user->two_factor_expires_at); // Convert the expiration date to Carbon
        
        $data = (object) [
            'token' => $token,
        ];

        // Check if the code matches and the expiration time is in the future
        if ($user->two_factor_code === $this->request->code && $expiresAt->isFuture()) {
            // Clear the code after successful verification
            $user->two_factor_code = null;
            $user->save();
            
            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired code'], 422);
    }
}
