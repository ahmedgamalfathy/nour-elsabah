<?php

namespace App\Http\Controllers\Api\V1\Website\Auth;

use App\Models\Otp\Otp;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\ClientUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordSendCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function sendCodeEmail(Request $request)
    {
        DB::beginTransaction();
        try{
            $clientUser =ClientUser::where("email", $request->email)->first();
            if(!$clientUser){
                return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
            }
            if($clientUser){
                $otps = Otp::where("email", $request->email)->get();
                if($otps->isNotEmpty()){
                    foreach($otps as $otp) {
                        $otp->delete();
                    }
                }
                $otp = Otp::create([
                    "email"=>$request->email,
                    "code"=>rand(1000,9999),
                    'expired_at' => now()->addMinutes(5),
                ]);
                Mail::to($request->email)->send(new ForgotPasswordSendCode($clientUser, $otp->code));
                $clientUserData =[
                    "clientId"=>$clientUser->id,
                    "name"=>$clientUser->name,
                    "email"=>$clientUser->email,
                ];
            }
            DB::commit();
            return ApiResponse::success($clientUserData ,__('auth.send_mail'));
            }catch(ValidationException $e){
                DB::rollBack();
             return ApiResponse::success([] ,__('auth.send_mail'));
            }catch(\Exception $ex){
                DB::rollBack();
                return ApiResponse::error(__('crud.server_error'),$ex->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
            }
    }
    public function verifyCodeEmail(Request $request)
    {
        DB::beginTransaction();
        try {
            $data= $request->validate([
                'code' => 'required|exists:otps,code',
                'clientId'=>['required','exists:client_user,id']
            ]);
           $client = ClientUser::findOrFail($data['clientId']);
            $otp= Otp::where('email',$client->email)->first();
           if($otp->code != $data['code']){
             return   ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
           }
           if($otp->expired_at < now()){
               return   ApiResponse::error(__('auth.code_expired'),[],HttpStatusCode::UNPROCESSABLE_ENTITY);
            }
            DB::commit();
            return ApiResponse::success([],__('crud.created'));
        } catch (\Throwable $th) {
           DB::rollBack();
           return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }
    public function resendCode(Request $request)
    {
        try{
            $client=ClientUser::where('id',$request->clientId)->first();
            $otp= Otp::where('email',$client->email)->first();
            if($otp){
                $otp->delete();
            }
            if($client){
               $code=rand(1000,9999);
               $otp= Otp::create([
                'email'=>$client->email,
                'code'=>$code,
                'expired_at'=>now()->addMinutes(5)
                ]);
                Mail::to($client->email)->send(new ForgotPasswordSendCode($client, $code));
                return ApiResponse::success([],__('crud.created'));
            }else{
              return   ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
            }
            }catch(\Exception $ex){
                return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
            }
    }
    public function newPassword(Request $request){
        DB::beginTransaction();
        try{
        $data=$request->validate([
            'clientId'=>['required','exists:client_user,id'],
            'password'=>['required','confirmed',Password::min(8)]
        ]);
        $client = ClientUser::findOrFail($data['clientId']);
        $otp= Otp::where('email',$client->email)->first();
        if(!$client){
           return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        if($otp->expired_at < now()){
          return ApiResponse::error(__('auth.code_expired'),[],HttpStatusCode::UNPROCESSABLE_ENTITY);
        }
        $client->update([
            'password'=>Hash::make($request->input('password')),
        ]);
        $otp->delete();
        DB::commit();
          return ApiResponse::success($client);
        }catch(\Exception $ex){
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
