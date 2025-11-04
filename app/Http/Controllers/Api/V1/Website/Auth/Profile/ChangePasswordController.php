<?php

namespace App\Http\Controllers\Api\V1\Website\Auth\Profile;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Requests\Client\ChagePasswordRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class ChangePasswordController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }
    /**
     * Handle password change request.
     */
    public function __invoke(ChagePasswordRequest $request)
    {
       $data =$request->validated();
        $authUser = $request->user();

        if (!Hash::check($data['currentPassword'], $authUser->password)) {
            return ApiResponse::error(__('password.current_password'), HttpStatusCode::UNPROCESSABLE_ENTITY);
        }

        $authUser->update([
            'password' => Hash::make($data['password']),
        ]);

        $authUser->tokens()->delete();

        return ApiResponse::success([], __('crud.updated'));
    }
}
