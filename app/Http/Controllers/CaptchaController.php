<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // For making HTTP requests
use Illuminate\Support\Facades\Validator;

class CaptchaController extends Controller
{
    public function store(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'personality.first_name' => 'required',
            'personality.last_name' => 'required',
            'personality.middle_name' => 'required',
            'personality.email' => 'required|email',
            'recaptchaResponse' => 'required', // Validate presence of reCAPTCHA response
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Verify the reCAPTCHA response using the provided g-recaptcha-response token
        $recaptchaResponse = $request->input('recaptchaResponse'); // Adjust to match your form field name
        $isVerified = $this->verifyCaptcha($recaptchaResponse);

        if (!$isVerified) {
            return back()->withErrors(['recaptchaResponse' => 'Invalid reCAPTCHA. Please try again.']);
        }

        // If validation passes and reCAPTCHA is verified, proceed with processing the form
        $firstName = $request->input('personality.first_name');
        $lastName = $request->input('personality.last_name');
        $middleName = $request->input('personality.middle_name');
        $email = $request->input('personality.email');

        // Your logic to create the user or store the data
        // Example:
        // User::create([
        //     'first_name' => $firstName,
        //     'last_name' => $lastName,
        //     'middle_name' => $middleName,
        //     'email' => $email,
        // ]);

        return redirect()->route('some.route')->with('success', 'User created successfully');
    }

    /**
     * Verify the reCAPTCHA response.
     *
     * @param  string  $recaptchaResponse
     * @return bool
     */
    public function verifyCaptcha($recaptchaResponse)
    {
        $secret = env('RECAPTCHA_SECRETKEY'); // Ensure this is set in your .env file
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $recaptchaResponse
        ]);
    
        // Return true if the reCAPTCHA verification is successful, otherwise false
        return $response->json()['success'] ?? false;
    }
}