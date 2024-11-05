<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Interface\Service\AuthenticationServiceInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function login(AuthRequest $request)
    {
        return $this->authenticationService->authenticate($request);
    }

    public function logout(Request $request)
    {
        return $this->authenticationService->unauthenticate($request);
    }
}
