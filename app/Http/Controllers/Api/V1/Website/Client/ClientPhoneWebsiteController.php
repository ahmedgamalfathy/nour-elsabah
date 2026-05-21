<?php

namespace App\Http\Controllers\Api\V1\Website\Client;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\ClientUser;
use App\Models\Client\ClientPhone;

use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Services\Client\ClientPhoneService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
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
        $clientId = request()->user()->client_id;
        $clientPhone = ClientPhone::where('id', $id)
            ->where('client_id', $clientId)
            ->firstOrFail();

        return ApiResponse::success(new ClientContactResource($clientPhone));
    }


    public function store(CreateClientContactRequest $createClientContactRequest)
    {
        try{
            $data = $createClientContactRequest->validated();
            $data['clientId'] = $createClientContactRequest->user()->client_id;
            $this->clientPhoneService->createClientPhone($data);
            return ApiResponse::success([], __('crud.created'),  HttpStatusCode::CREATED);
        }catch(\Throwable $th){
            Log::error($th->getMessage(), ['exception' => $th]);
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $id,UpdateClientContactWebsiteRequest $updateClientContactWebsiteRequest )
    {
        try{
            $clientId = $updateClientContactWebsiteRequest->user()->client_id;
            ClientPhone::where('id', $id)
                ->where('client_id', $clientId)
                ->firstOrFail();

            $data =$updateClientContactWebsiteRequest->validated();
            $data['clientId'] = $clientId;
            $this->clientPhoneService->updateClientPhone($id, $data);
            return ApiResponse::success([], __('crud.updated'));
        }catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }
        catch(\Throwable $th){
            Log::error($th->getMessage(), ['exception' => $th]);
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(int $id)
    {
        try{
            ClientPhone::where('id', $id)
                ->where('client_id', request()->user()->client_id)
                ->firstOrFail();

            $this->clientPhoneService->deleteClientPhone($id);
            return  ApiResponse::success([], __('crud.deleted'),  HttpStatusCode::OK);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }catch(\Throwable $th){
            Log::error($th->getMessage(), ['exception' => $th]);
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
