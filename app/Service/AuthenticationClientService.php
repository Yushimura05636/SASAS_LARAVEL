<?php

namespace App\Service;

use App\Http\Resources\AuthResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerResourceCollection;
use App\Interface\Repository\CustomerRepositoryInterface;
use App\Interface\Repository\PersonalityRepositoryInterface;
use App\Interface\Service\AuthenticationClientServiceInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthenticationClientService implements AuthenticationClientServiceInterface
{
    private $customerRepository;
    private $personalityRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository, PersonalityRepositoryInterface $personalityRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->personalityRepository = $personalityRepository;
    }

public function authenticate(object $payload)
{ 
    // Get the personality record based on email
    $personality = $this->personalityRepository->findByEmail($payload->email);

    // return response()->json([
    //     'personality' => $personality
    // ]);


    if (!$personality) {
        return response()->json([
            'error' => 'No customer account found with this email'
        ], Response::HTTP_UNAUTHORIZED);
    }

    // Get the customer record based on personality_id
    $customer = $this->customerRepository->findByPersonalityId($personality->id);
    
    if (!$customer) {
        return response()->json([
            'error' => 'No associated customer found'
        ], Response::HTTP_UNAUTHORIZED);
    }

    // Check the password in the customer table
    if (!Hash::check($payload->password, $customer->password)) {
        return response()->json([
            'error' => 'Invalid Credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }


    // Generate token if needed
    // $token = $customer->createToken('auth-token')->plainTextToken;

    $data = (object) [
        // 'token' => $token,
        'user' => new CustomerResource($customer)
    ];

    // return response()->json([
    //     'customer' => $customer
    // ]);

    return new AuthResource($data);
}


    public function unauthenticate(object $payload)
    {
        $payload->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully Logged out'
        ], Response::HTTP_OK);
    }
}
