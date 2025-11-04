<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Client;

use App\Enums\IsMain;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;

class ClientCheckDefaultController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

            $client=  Client::with(['emails', 'phones', 'addresses'])->find($request->clientId);
            if(!$client){
                return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
            }
            $email = $client->emails->where('is_main', IsMain::PRIMARY->value)->first();
            $phone = $client->phones->where('is_main', IsMain::PRIMARY->value)->first();
            $address = $client->addresses->where('is_main', IsMain::PRIMARY->value)->first();
            $response = [
                "clientEmail" => $email ? [
                    "clientEmailId" => $email->id,
                    "isMain" => $email->is_main,
                    "email" => $email->email,
                ] : "",

                "clientPhone" => $phone ? [
                    "clientPhoneId" => $phone->id,
                    "isMain" => $phone->is_main,
                    "phone" => $phone->phone,
                ] : "",

                "clientAddress" => $address ? [
                    "clientAddressId" => $address->id,
                    "isMain" => $address->is_main,
                    "address" => $address->address,
                    "region"=>$address->region,
                    "city"=>$address->city,
                    "streetNumber"=>$address->street_number,
                ] : "",
            ];

       return ApiResponse::success($response);
    }
}
