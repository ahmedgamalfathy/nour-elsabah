<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Client;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Services\Client\ClientEmailService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Client\ClientEmails\ClientEmailResource;
use App\Http\Requests\Client\ClientEmail\CreateClientEmailRequest;
use App\Http\Requests\Client\ClientEmail\UpdateClientEmailRequest;
use App\Http\Resources\Client\ClientEmails\AllClientEmailCollection;

class ClientEmailController extends Controller implements HasMiddleware
{
    protected $clientEmailService;
 public function __construct(ClientEmailService $clientEmailService)
 {
     $this->clientEmailService = $clientEmailService;
 }
 public static function middleware(): array
 {
     return [
         new Middleware('auth:api'),
         new Middleware('permission:all_client_emails', only:['index']),
         new Middleware('permission:create_client_email', only:['create']),
         new Middleware('permission:edit_client_email', only:['edit']),
         new Middleware('permission:update_client_email', only:['update']),
         new Middleware('permission:destroy_client_email', only:['destroy']),
     ];
 }
    public function index(Request $request)
    {

        $ClientEmail = $this->clientEmailService->allClientEmails($request->clientId);
        return ApiResponse::success(new AllClientEmailCollection(PaginateCollection::paginate( $ClientEmail, $request->pageSize?$request->pageSize:10)));
    }
    public function store(CreateClientEmailRequest $createClientEmailRequest)
    {
        try{
            $this->clientEmailService->createClientEmail($createClientEmailRequest->validated());
            return ApiResponse::success([], __('crud.created'), HttpStatusCode::CREATED);
        }catch (\Exception $e) {
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }
    public function show(int $id)
    {
        try {
            $clientEmail = $this->clientEmailService->editClientEmail($id);
            return ApiResponse::success(new ClientEmailResource($clientEmail));
        }catch (ModelNotFoundException $th) {
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }



    }
    public function update(int $id,UpdateClientEmailRequest $updateClientEmailRequest)
    {
        $ClientEmail = $this->clientEmailService->updateClientEmail($id, $updateClientEmailRequest->validated());
        if(!$ClientEmail){
            return ApiResponse::error( __('crud.not_found'),[], HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success([], __('crud.updated'));
    }
    public function destroy(int $id)
    {
        try{
          $this->clientEmailService->deleteClientEmail($id);
          return ApiResponse::success([], __('crud.deleted'), HttpStatusCode::OK);
        }catch (ModelNotFoundException $e) {
        return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }catch (\Exception $e) {
        return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }

}
