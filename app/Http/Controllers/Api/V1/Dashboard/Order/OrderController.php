<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Order;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Resources\Order\OrderResource;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\Order\AllOrderCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller implements HasMiddleware
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('permission:all_orders', only:['index']),
            new Middleware('permission:create_order', only:['store']),
            new Middleware('permission:edit_order', only:['edit']),
            new Middleware('permission:update_order', only:['update']),
            new Middleware('permission:destroy_order', only:['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $orders= $this->orderService->allOrders();
        return ApiResponse::success(new AllOrderCollection(PaginateCollection::paginate($orders,$request->pageSize?$request->pageSize:10)));
    }

    public function show($id)
    {
        try {
            $order=$this->orderService->editOrder($id);
            return ApiResponse::success(new OrderResource($order));
        }catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function store(CreateOrderRequest $createOrderRequest)
    {
        try {
            DB::beginTransaction();
            $result = $this->orderService->createOrder($createOrderRequest->validated());

            if (!empty($result['availableQuantity'])) {
                DB::rollBack();
                return ApiResponse::error(
                    __('crud.no_available_quantity'),
                    $result['availableQuantity'],
                    HttpStatusCode::UNPROCESSABLE_ENTITY
                );
            }
            DB::commit();
            return ApiResponse::success([],__('crud.created'));
        } catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),$th->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }

    public function update(UpdateOrderRequest $updateOrderRequest, $id)
    {
        try {
            DB::beginTransaction();
            $order = $this->orderService->updateOrder($id, $updateOrderRequest->validated());
            if(isset($order['availableQuantity']) && count($order['availableQuantity'])){
                return ApiResponse::error(__('crud.no_available_quantity'),$order,HttpStatusCode::UNPROCESSABLE_ENTITY);
            }
            DB::commit();
            return ApiResponse::success([],__('crud.updated'));
        } catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(int $id)
    {
        try {
            $this->orderService->deleteOrder($id);
            return ApiResponse::success([],__('crud.deleted'));
        }catch(ModelNotFoundException $e){
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $th) {
            return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }
}
