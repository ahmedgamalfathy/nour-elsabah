<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\User\UserType;
use App\Helpers\ApiResponse;
use App\Enums\User\UserStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\User\LoggedInUserResource;
use App\Services\UserRolePremission\UserPermissionService;
use App\Services\Upload\UploadService;
use Illuminate\Http\UploadedFile;
class AuthService
{
    protected $userPermissionService;
    protected $uploadService;

    public function __construct(UserPermissionService $userPermissionService ,UploadService $uploadService)
    {
        $this->userPermissionService = $userPermissionService;
        $this->uploadService = $uploadService;
    }
    public function register(array $userData)
    {
        $avatarPath = null;
        if(isset($userData['avatar']) && $userData['avatar'] instanceof UploadedFile){
            $avatarPath =  $this->uploadService->uploadFile($userData['avatar'], 'avatars');
        }

        $user = User::create([
            'name' => $userData['name'],
            'username' => $userData['username'],
            'email' => $userData['email']??null,
            'phone' => $userData['phone']??null,
            'address' => $userData['address']??null,
            'password' => $userData['password'],
            'is_active' => UserStatus::from($userData['isActive'])->value,
            'avatar' => $avatarPath,
        ]);

        $role = Role::find($userData['roleId']);
        $user->assignRole($role->id);

        return $user;


    }
    public function login(array $data)
    {
        try {
            $user = User::where('username', $data['username'])
            ->orWhere('email',$data['username'])->first();
            if (!$user || !Hash::check($data['password'], $user->password)) {
                return ApiResponse::error(__('auth.failed'), [], HttpStatusCode::UNAUTHORIZED);
            }
            if ($user->is_active == UserStatus::INACTIVE ) {
                return ApiResponse::error(__('auth.inactive_account'), [], HttpStatusCode::UNAUTHORIZED);
            }
            if($user->user_type !=UserType::ADMIN->value){
                return ApiResponse::error(__('auth.unauthorized_access'), [], HttpStatusCode::UNAUTHORIZED);
            }
            // // Revoke old tokens (optional)
            // $user->tokens()->delete();

            // Generate a new token (DO NOT return it directly)
            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::success([
                'profile' => new LoggedInUserResource($user),
                'role' => $user->roles->first()->name,
                'permissions' => $this->userPermissionService->getUserPermissions($user),
                'tokenDetails' => [
                    'token' => $token,
                    'expiresIn' => 1440
                ],
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        $user = auth()->user();

        if ($user) {
            $user->tokens()->delete(); // Revoke all tokens
        }

        return ApiResponse::success([], __('auth.logged_out'));
    }
}
