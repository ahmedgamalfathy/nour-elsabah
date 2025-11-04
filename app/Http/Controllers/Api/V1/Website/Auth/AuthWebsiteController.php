<?php

namespace App\Http\Controllers\Api\V1\Website\Auth;

use App\Models\User;
use App\Enums\IsActive;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Enums\Client\ClientType;
use Illuminate\Http\UploadedFile;
use App\Enums\Client\ClientStatus;
use App\Enums\Order\OrderStatus;
use App\Services\User\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\Upload\UploadService;
use Illuminate\Validation\Rules\Password;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Resources\Client\LoggedInClientResource;
use App\Models\Client\ClientUser;

class AuthWebsiteController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function register(Request $request){
       $data= $request->validate([
            "name"=>"required|string",
            "email"=>"required|string|email|unique:client_user,email",
            "password"=>["required",
            Password::min(8)->mixedCase()->numbers()],
            // 'avatar' => ["sometimes", "nullable","image", "mimes:jpeg,jpg,png,gif,svg", "max:5120"],
        ]);
        $avatarPath = null;

        // if(isset($data['avatar']) && $data['avatar'] instanceof UploadedFile){
        //     $avatarPath =  $this->uploadService->uploadFile($data['avatar'], 'clientAvatars');
        // }
        $client = Client::create([
            "name" => $data['name'],
            "type" => ClientType::CLIENT->value
        ]);
      $user = ClientUser::create([
            "avatar"=>$avatarPath,
            "name"=>$data['name'],
            "password" =>Hash::make($data['password']),
            "email"=>$data['email'],
            "status"=> ClientStatus::ACTIVE->value,
            "client_id" =>$client->id
            ]);
        $token = $user->createToken(//now()->addDays(3) , now()->addHours(12)
            'register', ['*'], now()->addDays(1)
            )->plainTextToken;

        return ApiResponse::success([
            'profile' => new LoggedInClientResource($user),
            'tokenDetails' => [
                'token' => $token,
                'expiresIn' => 24 *60
            ],
        ],__('crud.created'));
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete(); // حذف جميع الرموز الخاصة بالمستخدم
        }
        return ApiResponse::success([], __('auth.logged_out'));
    }
    public function login(Request $request)
    {
        $remember = $request->boolean('remember', 0);
        $user = ClientUser::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error(__('auth.failed'), [], HttpStatusCode::UNAUTHORIZED);
        }
        if ($user->status == ClientStatus::INACTIVE) {
            return ApiResponse::error(__('auth.inactive_account'), [], HttpStatusCode::UNAUTHORIZED);
        }
        // $user->tokens()->delete();
        //check remember
        if($remember == 1){
            $token = $user->createToken(//now()->addDays(3) , now()->addHours(12)
                'login' )->plainTextToken;
        }else {
            $token = $user->createToken(//now()->addDays(3) , now()->addHours(12)
                'login' ,['*'],now()->addDays(1))->plainTextToken;
        }
        return ApiResponse::success([
            'profile' => new LoggedInClientResource($user),
            "orderIdInCart" => Client::where('id',$user->client_id)->first()->orders()->where('status',OrderStatus::IN_CART)->first()->id??"",
            'tokenDetails' => [
                'token' => $token,
                'expiresIn' => $remember == 1? "all the time" : 60*24,
            ],
        ]);
    }
}
