<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Client;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Services\Client\ClientAddressService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Resources\Client\ClientAddress\ClientAddressResource;
use App\Http\Requests\Client\ClientAddress\CreateClientAddressRequest;
use App\Http\Requests\Client\ClientAddress\UpdateClientAddressRequest;
use App\Http\Resources\Client\ClientAddress\AllClientAddressCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientAdressController extends Controller implements HasMiddleware
{
    protected $clientAddressService;
    public function __construct( ClientAddressService $clientAddressService)
    {
        $this->clientAddressService = $clientAddressService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('permission:all_client_addresses', only:['index']),
            new Middleware('permission:create_client_address', only:['create']),
            new Middleware('permission:edit_client_address', only:['edit']),
            new Middleware('permission:update_client_address', only:['update']),
            new Middleware('permission:destroy_client_address', only:['destroy']),
        ];
    }
    public function index(Request $request)
    {
            $clientAddresses = $this->clientAddressService->allClientAddress( $request->clientId);
            return ApiResponse::success(new AllClientAddressCollection(PaginateCollection::paginate( $clientAddresses, $request->pageSize?$request->pageSize:10)));
    }

    public function show(int $id)
    {
        $clientAddress = $this->clientAddressService->editClientAddress($id);
        if (!$clientAddress) {
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success(new ClientAddressResource($clientAddress));
    }
    public function store(CreateClientAddressRequest $createClientAddressRequest)
    {
        $this->clientAddressService->createClientAddress($createClientAddressRequest->validated());
        return ApiResponse::success([], __('crud.created'), HttpStatusCode::CREATED);
    }
    public function update(int $id,UpdateClientAddressRequest $updateClientAddressRequest)
    {
        $clientAddress = $this->clientAddressService->updateClientAddress($id, $updateClientAddressRequest->validated());
        if (!$clientAddress) {
            return ApiResponse::error(__('crud.not_found'), HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success([], __('crud.updated'));
    }
    public function destroy(int $id)
    {
        try {
            $this->clientAddressService->deleteClientAddress($id);
            return ApiResponse::success([], __('crud.deleted'));
        } catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'), HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),[], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }
}

