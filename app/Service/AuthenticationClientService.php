<?php

namespace App\Service;

use App\Http\Resources\AuthResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerResourceCollection;
use App\Http\Resources\UserResource;
use App\Interface\Repository\CustomerRepositoryInterface;
use App\Interface\Repository\PersonalityRepositoryInterface;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\AuthenticationClientServiceInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthenticationClientService implements AuthenticationClientServiceInterface
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(object $payload)
{
    $user = $this->userRepository->findByEmail($payload->email);

    if (!$user) {
        return response()->json([
            'message' => 'No account found'
        ], Response::HTTP_UNAUTHORIZED);
    }

    if (!is_null($user->employee_id))
    {
        return response()->json([
            'message' => 'No account found'
        ], Response::HTTP_UNAUTHORIZED);
    }

    if (!Hash::check($payload->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid Credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }

    // You can create a token here if needed
    //$token = $user->createToken('auth-token')->plainTextToken;

    $data = (object) [
        //'token' => $token,
        'user' => new UserResource($user)
    ];

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
