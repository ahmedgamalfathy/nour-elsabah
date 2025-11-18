<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use function Laravel\Prompts\form;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\Product\LimitedQuantity;
use App\Services\Coupon\CouponService;
use App\Services\Order\OrderItemService;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Resources\Order\Website\OrderResource;
use App\Http\Resources\Order\OrderItem\Website\OrderItemResource;

class AuthOrderController extends Controller implements HasMiddleware
{
    public $orderItemService;
    public $couponService;
    public function __construct(OrderItemService $orderItemService, CouponService $couponService)
    {
        $this->orderItemService = $orderItemService;
        $this->couponService = $couponService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }

    public function store(Request $request){
        try {
            DB::beginTransaction();
        $data =$request->validate([
                'orderItems' => 'required|array|min:1',
                'orderItems.*.productId' => 'required|integer|exists:products,id',
                'orderItems.*.qty' => 'required|integer|min:1',
        ]);
        $auth = $request->user();
        $client = Client::findOrFail($auth->client_id);
        if(!$auth){
            return ApiResponse::error("Unauthenticated");
        }
        $totalCost =0;
        $totalPrice = 0;
        $totalPriceAfterDiscount = 0;

        $order = Order::create([
            'discount' =>0.00,
            'discount_type' =>0,
            'client_phone_id' => $request->input("client.clientPhoneId")??null,
            'client_email_id' => $request->input("client.clientEmailId")??null ,
            'client_address_id' => $request->input("client.clientAddressId")??null,
            'client_id' => $client->id,
            'status' => 3,
        ]);

        $avilableQuantity = [];
        foreach ($data['orderItems'] as $itemData) {
            $item= $this->orderItemService->createOrderItem([
                    'orderId' => $order->id,
                    ...$itemData
                ]);
            if($item->product->is_limited_quantity == LimitedQuantity::LIMITED && $item->product->quantity < $item->qty){
                if ($item->product->quantity < $item->qty) {
                    $avilableQuantity[] = [
                        'productId' => $item->product->id,
                        'quantity' => $item->product->quantity,
                        'name' => $item->product->name
                    ];
                    return ["message"=>__('crud.no_available_quantity'),
                    $avilableQuantity];
                }
               //  $item->product->decrement('quantity', $item->qty);
            }
            $totalPrice += $item->price * $item->qty;
            $totalCost += $item->cost*$item->qty;
        }

        if ($order->discount_type == DiscountType::PERCENTAGE) {
            $totalPriceAfterDiscount = $totalPrice - ($totalPrice * ($data['discount'] / 100));
        } elseif ($order->discount_type == DiscountType::FIXCED) {
            $totalPriceAfterDiscount = $totalPrice - $data['discount'];
        }elseif($order->discount_type == DiscountType::NO_DISCOUNT){
            $totalPriceAfterDiscount = $totalPrice;
        }   
        $order->update([
            'price_after_discount' => $totalPriceAfterDiscount,
            'price' => $totalPrice,
            'total_cost'=>$totalCost,       
            'discount_type' => DiscountType::COUPON->value,
        ]);
        DB::commit();
        return ApiResponse::success(new OrderResource($order));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'),$e->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    public function show($id){
        $order = Order::findOrFail($id);
        if(!$order){
            return ApiResponse::error("Order not found");
        }
        return ApiResponse::success(new OrderResource($order));
    }
    public function update(Request $request, $id){

        $data =$request->validate([
            'client.clientPhoneId' => 'required|exists:client_phones,id',
            'client.clientEmailId' => 'required|exists:client_emails,id',
            'client.clientAddressId' => 'required|exists:client_addresses,id',
         ]);
        $order = Order::findOrFail($id);
        if(!$order){
            return ApiResponse::error("Order not found");
        }
        if($order->status == OrderStatus::CHECKOUT){
            return ApiResponse::error("Order already checkout");
        }
        $order->client_phone_id = $data['client']['clientPhoneId'];
        $order->client_email_id = $data['client']['clientEmailId'];
        $order->client_address_id = $data['client']['clientAddressId'];
        $order->status = OrderStatus::IN_CART->value;
        $order->save();
        return ApiResponse::success(new OrderResource($order));
    }
    public function couponCart(Request $request){
        $data =$request->validate([
        'couponCode' => 'required|exists:coupons,code',
        'orderId'=>'required|exists:orders,id'
        ]);
        $auth = $request->user();
        $client = Client::find($auth->client_id);
        if(!$client){
           return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $order = Order::find($data['orderId']);
        if(!$order){
          return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
            $couponDiscount = 0;
            if (!empty($data['couponCode'])) {
                $couponValidation = $this->couponService->validateCoupon(
                    $data['couponCode'],
                    $client->id,
                    $order->price_after_discount
                );

                if ($couponValidation['valid']) {
                    $couponDiscount = $couponValidation['discount'];
                    $this->couponService->applyCoupon(
                        $couponValidation['coupon'],
                        $order,
                        $client->id,
                        $couponDiscount
                    );
                }
                $priceAfterCoupon = $order->price_after_discount - $couponDiscount;
                $order->price_after_discount = $priceAfterCoupon;
                $order->save();
                return [
                 'priceBeforCoupon'=>$order->price,
                 'priceAfterCoupon'=>$order->price_after_discount
                ];
            }
    }
}
