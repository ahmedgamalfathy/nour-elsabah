<?php

namespace App\Http\Controllers\Api\V1\Website\Client;

use App\Helpers\ApiResponse;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\Client\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\Client\ClientService;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Requests\Client\CreateClientRequest;
use App\Http\Resources\Client\AllClientCollection;
use App\Models\Client\ClientUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controllers\Middleware;

class ClientWebsiteController extends Controller implements HasMiddleware
{
    protected $clientService;
   public function __construct( ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    public static function middleware()
    {
        return [
            // new Middleware('auth:client'),
        ];
    }

    public function show(Request $request)
    {
        $clientUserId = $request->user()->id;
        $clientId = ClientUser::where('id', $clientUserId)->first()->client_id;
        $client = $this->clientService->editClient($clientId);
        if (!$client) {
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success(new ClientResource($client));
    }
    public function update(int $id,UpdateClientRequest $updateClientRequest)
    {
        try {
            DB::beginTransaction();
            $clientUserId = $updateClientRequest->user()->id;
            $clientId = ClientUser::where('id', $clientUserId)->first()->client_id;
            $this->clientService->updateClient($clientId,$updateClientRequest->validated());
            DB::commit();
            return ApiResponse::success([],__('crud.updated'));
        } catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id)
    {
        try{
            $this->clientService->deleteClient($id);
            return ApiResponse::success([],__('crud.deleted'));
            }catch(ModelNotFoundException $e){
            return apiResponse::error(__('crud.not_found'),[], HttpStatusCode::NOT_FOUND);
            }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
            }

    }
    

}
