<?php

namespace App\Http\Controllers;

use App\Models\User_Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Twilio\Rest\Client;

class PhoneVerificationController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function sendPhoneVerification()
    {
        $user = User_Account::where('email', $this->request->email)->first();

        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        try {
            // Send SMS verification code
            $verification = $twilio->verify->v2->services(env('TWILIO_VSID'))
                ->verifications
                ->create($this->request->phone_number, "sms");

            // Save the code and expiration
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->save();

            // Check if the status is "pending" (indicating the message was sent)
            if ($verification->status === 'pending') {
                return response()->json(['message' => 'Verification code sent successfully.']);
            } else {
                return response()->json(['error' => 'Failed to send verification code.'], 500);
            }
        } catch (\Exception $e) {
            // Handle any errors from the Twilio API
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    }

    public function verifyPhoneCode()
    {
        $user = User_Account::where('email', $this->request->email)->first();

        // Send SMS using Twilio
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        // Verify the user-provided code
        $verification_check = $twilio->verify->v2->services(env('TWILIO_VSID'))
        ->verificationChecks
        ->create([
            "to" => '+63' . $this->request->phone_number,
            "code" => $this->request->code
        ]);

            //return response()->json(['success' => false, 'message' => $verification_check->status], 400);

        if ($verification_check->status == 'approved') {
            $token = $user->createToken('auth-token')->plainTextToken;
            $data = (object) [
                'token' => $token,
            ];

            // Save the code and expiration
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->save();

            return response()->json(['success' => true, 'message' => `Code verified successfully.`, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid code or code expired.'], 400);
        }
    }
}
