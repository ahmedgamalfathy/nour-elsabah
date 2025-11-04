<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Client;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;

use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Services\Client\ClientPhoneService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Client\ClientContact\ClientContactResource;
use App\Http\Requests\Client\ClientContact\CreateClientContactRequest;
use App\Http\Resources\Client\ClientContact\AllClientContactCollection;

class ClientPhoneController extends Controller implements HasMiddleware
{
     public $clientPhoneService;
     public function __construct(ClientPhoneService $clientPhoneService)
     {
         $this->clientPhoneService = $clientPhoneService;
     }
     public static function middleware(): array
     {
         return [
             new Middleware('auth:api'),
             new Middleware('permission:all_client_phones', only:['index']),
             new Middleware('permission:create_client_phone', only:['create']),
             new Middleware('permission:edit_client_phone', only:['edit']),
             new Middleware('permission:update_client_phone', only:['update']),
             new Middleware('permission:destroy_client_phone', only:['destroy']),
         ];
     }

    public function index(Request  $request)
    {
        $clientPhones = $this->clientPhoneService->allClientPhones($request->clientId);
        return ApiResponse::success(new AllClientContactCollection(PaginateCollection::paginate($clientPhones, $request->pageSize?$request->pageSize:10)));
    }

    public function show(int $id)
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
            $this->clientPhoneService->createClientPhone($createClientContactRequest->validated());
            return ApiResponse::success([], __('crud.created'),  HttpStatusCode::CREATED);
        }catch(\Throwable $th){
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $id,CreateClientContactRequest $createClientContactRequest )
    {
        try{
            $this->clientPhoneService->updateClientPhone($id, $createClientContactRequest->validated());
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
            $this->clientPhoneService->deleteClientPhone($id);
            return  ApiResponse::success([], __('crud.deleted'),  HttpStatusCode::OK);
        }catch(ModelNotFoundException $e){

            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }catch(\Throwable $th){
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
