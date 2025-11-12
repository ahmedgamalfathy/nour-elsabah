<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OpenApi\Annotations as OA;

class AuthController extends Controller //implements HasMiddleware
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    public function login(LoginRequest $loginReq)
    {
        return $this->authService->login($loginReq->validated());
    }


    public function logout()
    {
        return $this->authService->logout();
    }
}
