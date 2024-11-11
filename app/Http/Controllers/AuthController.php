<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Interface\Service\AuthenticationClientServiceInterface;
use App\Interface\Service\AuthenticationServiceInterface;
use Illuminate\Http\Request;

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

    public function logout(Request $request)
    {
        return $this->authenticationService->unauthenticate($request);
    }

    public function clientLogin(AuthRequest $request)
    {
        return $this->authenticationClientService->authenticate($request);
    }
}
