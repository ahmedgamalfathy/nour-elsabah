<?php

namespace App\Http\Controllers\Api\V1\Website\Client;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Models\Client\ClientUser;
use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Models\Client\ClientAdrress;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Services\Client\ClientAddressService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Client\ClientAddress\ClientAddressResource;
use App\Http\Requests\Client\ClientAddress\CreateClientAddressRequest;
use App\Http\Requests\Client\ClientAddress\UpdateClientAddressRequest;
use App\Http\Resources\Client\ClientAddress\AllClientAddressCollection;
use App\Http\Requests\Client\ClientAddress\Website\CreateClientAddressWebsiteRequest;
use App\Http\Requests\Client\ClientAddress\Website\UpdateClientAddressWebsiteRequest;

class ClientAdressWebsiteController extends Controller implements HasMiddleware
{
    protected $clientAddressService;
    public function __construct( ClientAddressService $clientAddressService)
    {
        $this->clientAddressService = $clientAddressService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }
    public function index(Request $request)
    {

           $clienUsertId = $request->user()->id;
            $clientId = ClientUser::where('id', $clienUsertId)->first()->client_id;
            $clientAddresses = $this->clientAddressService->allClientAddress( $clientId);
            return ApiResponse::success(new AllClientAddressCollection(PaginateCollection::paginate( $clientAddresses, $request->pageSize?$request->pageSize:10)));
    }

    public function show(int $id , Request $request)
    {
        $clienUsertId = $request->user()->id;
        $clientId = ClientUser::where('id', $clienUsertId)->first()->client_id;
        $clientAddress = ClientAdrress::find($id);
        if($clientId != $clientAddress->client_id){
            return ApiResponse::error(__('crud.not_found'), HttpStatusCode::NOT_FOUND);
        }
        if (!$clientId) {
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $clientAddress = $this->clientAddressService->editClientAddress($id);
        if (!$clientAddress) {
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success(new ClientAddressResource($clientAddress));
    }
    public function store(CreateClientAddressWebsiteRequest $createClientAddressWebsiteRequest)
    {
        $clientUserId = $createClientAddressWebsiteRequest->user()->id;
        $clientId = ClientUser::where('id', $clientUserId)->first()->client_id;
        $data = $createClientAddressWebsiteRequest->validated();
        $data['clientId'] = $clientId;
        $this->clientAddressService->createClientAddress($data);
        return ApiResponse::success([], __('crud.created'), HttpStatusCode::CREATED);
    }
    public function update(int $id,UpdateClientAddressWebsiteRequest $updateClientAddressWebsiteRequest)
    {
        $clientUserId = $updateClientAddressWebsiteRequest->user()->id;
        $clientId = ClientUser::where('id', $clientUserId)->first()->client_id;
        $data = $updateClientAddressWebsiteRequest->validated();
        $data['clientId'] = $clientId;
        $clientAddress = $this->clientAddressService->updateClientAddress($id, $data);
        if (!$clientAddress) {
            return ApiResponse::error(__('crud.not_found'), HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success([], __('crud.updated'));
    }
    public function destroy(int $id , Request $request)
    {
        try {
            $clientUserId = $request->user()->id;
            $clientId = ClientUser::where('id', $clientUserId)->first()->client_id;
            $clientAddress = ClientAdrress::find($id);
            if($clientId != $clientAddress->client_id){
                return ApiResponse::error(__('crud.not_found'), HttpStatusCode::NOT_FOUND);
            }
            $this->clientAddressService->deleteClientAddress($id);
            return ApiResponse::success([], __('crud.deleted'));
        } catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'), HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),[], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }
}

