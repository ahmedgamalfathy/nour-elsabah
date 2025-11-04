<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Client;

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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Controllers\Middleware;

class ClientController extends Controller implements HasMiddleware
{
    protected $clientService;
   public function __construct( ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('permission:all_clients', only:['index']),
            new Middleware('permission:create_client', only:['create']),
            new Middleware('permission:edit_client', only:['edit']),
            new Middleware('permission:update_client', only:['update']),
            new Middleware('permission:destroy_client', only:['destroy']),
        ];
    }

    public function index(Request $request)
    {
         $clients = $this->clientService->allClients();
        //  dd($clients->count());
         return ApiResponse::success(new AllClientCollection($clients));
    }
    public function show(int $id)
    {
        $client = $this->clientService->editClient($id);
        if (!$client) {
            return apiResponse::error(__('crud.not_found'),[], HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success(new ClientResource($client));
    }
    public function store(CreateClientRequest $createClientRequest)
    {

        try {
            DB::beginTransaction();
            $this->clientService->createClient($createClientRequest->validated());
            DB::commit();

            return ApiResponse::success([],__('crud.created'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'),$th->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    public function update(int $id,UpdateClientRequest $updateClientRequest)
    {
        try {
            DB::beginTransaction();
            $this->clientService->updateClient($id,$updateClientRequest->validated());
            DB::commit();
            return ApiResponse::success([],__('crud.updated'));
        }catch( ModelNotFoundException $e ){
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),$th->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
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
        return ApiResponse::error(__('crud.server_error'),$th->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    public function restore($id){
        try {
            $this->clientService->restoreClient($id);
            return ApiResponse::success([],__('crud.restore'));
        }catch(ModelNotFoundException $e){
            return apiResponse::error(__('crud.not_found'),[], HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),$th->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    public function forceDelete($id)
    {
        try {
            $this->clientService->forceDeleteClient($id);
            return ApiResponse::success([],__('crud.deleted'));
        } catch(ModelNotFoundException $e){
            return apiResponse::error(__('crud.not_found'),[], HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),$th->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

}
