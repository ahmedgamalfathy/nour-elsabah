<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Order;

use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Resources\Order\OrderResource;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\Api\V1\Dashboard\Order\BulkUpdateOrderStatusRequest;
use App\Http\Requests\Api\V1\Dashboard\Order\StoreOrderRequest;
use App\Http\Requests\Api\V1\Dashboard\Order\UpdateOrderRequest;
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
        return ApiResponse::success(new AllOrderCollection(PaginateCollection::paginate($orders,$request->pageSize?$request->pageSize:30)));
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

    public function store(StoreOrderRequest $createOrderRequest)
    {
        try {
            DB::beginTransaction();
            $this->orderService->createOrder($createOrderRequest->validated());
            DB::commit();

            return ApiResponse::success([], __('crud.created'));
        } catch (\App\Exceptions\InsufficientStockException $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            $payload = json_decode($e->getMessage(), true);
            return ApiResponse::error(
                'الكمية يجب أن تكون من مضاعفات وحدة البيع للمنتج.',
                $payload['quantityErrors'] ?? [],
                HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $th->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateOrderRequest $updateOrderRequest, $id)
    {
        try {
            DB::beginTransaction();
            $this->orderService->updateOrder($id, $updateOrderRequest->validated());
            DB::commit();

            return ApiResponse::success([], __('crud.updated'));
        } catch (\App\Exceptions\InsufficientStockException $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            $payload = json_decode($e->getMessage(), true);
            return ApiResponse::error(
                'الكمية يجب أن تكون من مضاعفات وحدة البيع للمنتج.',
                $payload['quantityErrors'] ?? [],
                HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
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
    public function bulkUpdateStatus(BulkUpdateOrderStatusRequest $request)
    {
        try {
            $data = $request->validated();
                $orders = Order::whereIn('id', $data['ids'])
                ->with(['items.product', 'client','clientEmail','clientAddress','clientPhone']) 
                ->get();
            
            // 2. المرور على كل طلب وتحديثه الفردي الصارم لتفعيل الـ Observer
            foreach ($orders as $order) {
                // نغير الحالة
                $order->status = $data['action'];
                
                // حفظ الطلب -> هنا سينطلق الـ Observer وهو يمتلك كافة بيانات الـ items والمخزون جاهزة في الذاكرة
                $order->save();
            }
            return ApiResponse::success([], __('crud.updated'));
        } catch (\Throwable $e) {
            return ApiResponse::error(
                __('crud.server_error'),
                $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }
}
