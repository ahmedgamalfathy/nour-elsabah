<?php

namespace App\Http\Controllers\Api\V1\Website\Client;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\ClientUser;

use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Services\Client\ClientPhoneService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Client\ClientContact\ClientContactResource;
use App\Http\Requests\Client\ClientContact\CreateClientContactRequest;
use App\Http\Resources\Client\ClientContact\AllClientContactCollection;
use App\Http\Requests\Client\ClientContact\UpdateClientContactWebsiteRequest;

class ClientPhoneWebsiteController extends Controller implements HasMiddleware
{
     public $clientPhoneService;
     public function __construct(ClientPhoneService $clientPhoneService)
     {
         $this->clientPhoneService = $clientPhoneService;
     }
     public static function middleware(): array
     {
         return [
             new Middleware('auth:client'),
         ];
     }

    public function index(Request $request)
    {
        $clientUserId = $request->user()->id;
        $clientId = ClientUser::where('id', $clientUserId)->first()->client_id;
        $clientPhones = $this->clientPhoneService->allClientPhones($clientId);
        return ApiResponse::success(new AllClientContactCollection(PaginateCollection::paginate($clientPhones, $request->pageSize ? $request->pageSize : 10)));
    }
    public function show($id)
    {
        $clientPhone = $this->clientPhoneService->editClientPhone($id);
        if (!$clientPhone) {
            return apiResponse::error(__('crud.not_found'), HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success(new ClientContactResource($clientPhone));
    }


    public function store(CreateClientContactRequest $createClientContactRequest)
    {
        try{
            $data = $createClientContactRequest->validated();
            // $clientUserId = $createClientContactRequest->user()->id;
            // $clientId = ClientUser::where('id', $clientUserId)->first()->client_id;
            // $data['clientId'] = $clientId;
            $this->clientPhoneService->createClientPhone($data);
            return ApiResponse::success([], __('crud.created'),  HttpStatusCode::CREATED);
        }catch(\Throwable $th){
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $id,UpdateClientContactWebsiteRequest $updateClientContactWebsiteRequest )
    {
        try{
            $userId =$updateClientContactWebsiteRequest->user()->id;
            $clientId = ClientUser::where('id', $userId)->first()->client_id;
            $data =$updateClientContactWebsiteRequest->validated();
            $data['clientId'] = $clientId;
            $this->clientPhoneService->updateClientPhone($id, $data);
            return ApiResponse::success([], __('crud.updated'));
        }catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }
        catch(\Throwable $th){
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(int $id)
    {
        try{
            $clientUserId = request()->user()->id;
            if(!$clientUserId){
                return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
            }
            $this->clientPhoneService->deleteClientPhone($id);
            return  ApiResponse::success([], __('crud.deleted'),  HttpStatusCode::OK);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }catch(\Throwable $th){
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
