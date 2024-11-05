<?php

namespace App\Http\Controllers;

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
        // Generate a 6-digit verification code
        $code = random_int(100000, 999999);

        // Store the code in cache with an expiration time
        Cache::put('sms_verification_code_' . $this->request->phone_number, $code, now()->addMinutes(10));

        // Send SMS using Twilio
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        $twilio->messages->create($this->request->phone_number, [
            'MessagingServiceSid' => 'MGb7c6095df324624d8d3f470049ad12b0',
            'body' => "Your verification code is: $code"
        ]);

        return response()->json(['message' => 'Verification code sent successfully.']);
    }

    public function verifyPhoneCode()
    {
        // Retrieve code from cache
        $cachedCode = Cache::get('sms_verification_code_' . $this->request->phone_number);

        $token = $user->createToken('auth-token')->plainTextToken;

        $data = (object) [
            'token' => $token,
        ];

        if ($cachedCode && $cachedCode == $this->request->code) {
            // Clear the code from cache after successful verification
            Cache::forget('sms_verification_code_' . $this->request->phone_number);

            return response()->json(['success' => true, 'message' => `Code verified successfully.`, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid code or code expired.'], 400);
    }
}
