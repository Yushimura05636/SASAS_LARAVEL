<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\AuthenticationClientServiceInterface;
use App\Interface\Service\AuthenticationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private $authenticationService;
    private $authenticationClientService;

    public function __construct(AuthenticationServiceInterface $authenticationService, AuthenticationClientServiceInterface $authenticationClientService )
    {
        $this->authenticationService = $authenticationService;
        $this->authenticationClientService = $authenticationClientService;
    }

    public function login(AuthRequest $request)
    {
        return $this->authenticationService->authenticate($request);
    }

    public function loginDebug(Request $payload, UserRepositoryInterface $userRepository)
    {
        $user = $userRepository->findByEmail($payload->email);

        if (!$user) {
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
        $token = $user->createToken('auth-token')->plainTextToken;

        $data = (object) [
            'token' => $token,
            'user' => new UserResource($user)
        ];

        return new AuthResource($data);
    }

    public function logout(Request $request)
    {
        return $this->authenticationService->unauthenticate($request);
    }

    public function clientLogin(AuthRequest $request)
    {
        return $this->authenticationClientService->authenticate($request);
    }
}
