<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use App\Models\Client\ClientUser;
use function Laravel\Prompts\form;
use App\Http\Controllers\Controller;
use App\Enums\Product\LimitedQuantity;

use App\Services\Order\OrderItemService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Resources\Order\Website\OrderResource;
use App\Http\Resources\Order\OrderItem\Website\OrderItemResource;

class AuthOrderItemController extends Controller implements HasMiddleware
{
    public $orderItemService;
    public function __construct(OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }
    public function index(Request $request)
    {
    $auth = $request->user();
    $clientId = ClientUser::findOrFail($auth->id);
    $inCartOrder= Order::where('status',OrderStatus::IN_CART)->where('client_id',$clientId->client_id)->first();
    $orderItems = $inCartOrder->items;
    return ApiResponse::success(OrderItemResource::collection($orderItems));
    }
    public function edit($id){
       $orderItem = OrderItem::findOrFail($id);
       if(!$orderItem){
        return ApiResponse::error(__('crud.not_found'));
       }
       return ApiResponse::success(new OrderItemResource($orderItem));

    }
    public function store(Request $request){
        $data =$request->validate([
        'productId' => 'required|integer|exists:products,id',
        'qty' => 'required|integer|min:1',
        'orderId'=>'required|integer|min:1'
        ]);
        $auth = $request->user();
        $client = Client::findOrFail($auth->client_id);
        $product = Product::where('id', $data['productId'])->select(['cost','price'])->first();
        $orderItem = OrderItem::create([
            'order_id' => $data['orderId'],
            'product_id' => $data['productId'],
            'price' => $product->price,
            'cost'=> $product->cost,
            'qty' => $data['qty'],
        ]);
        $avilableQuantity = [];
        if($orderItem->product->is_limited_quantity == LimitedQuantity::LIMITED && $orderItem->product->quantity < $orderItem->qty){
            if ($orderItem->product->quantity < $orderItem->qty) {
                $avilableQuantity[] = [
                    'productId' => $orderItem->product->id,
                    'quantity' => $orderItem->product->quantity,
                    'name' => $orderItem->product->name
                ];
                return  $avilableQuantity;
            }
           //  $item->product->decrement('quantity', $item->qty);
        }
        $order = Order::findOrFail($orderItem->order_id);
        $totalCost =$order->total_cost;
        $totalPrice = $order->price;
        $totalPriceAfterDiscount = $order->price_after_discount;

        $totalPrice += $orderItem->price * $orderItem->qty;
        $totalCost += $orderItem->cost*$orderItem->qty;

        if ($order->discount_type == DiscountType::PERCENTAGE) {
            $totalPriceAfterDiscount = $totalPrice - ($totalPrice * ($order->discount / 100));
        } elseif ($order->discount_type == DiscountType::FIXCED) {
            $totalPriceAfterDiscount = $totalPrice - $order->discount;
        }elseif($order->discount_type == DiscountType::NO_DISCOUNT){
            $totalPriceAfterDiscount = $totalPrice;
        }
        $order->update([
            'price_after_discount' => $totalPriceAfterDiscount,
            'price' => $totalPrice,
            'total_cost'=>$totalCost
        ]);
        return ApiResponse::success(__('crud.created'));
    }
    public function update(Request $request ,$id)
    {
        $data =$request->validate([
        'productId' => 'required|integer|exists:products,id',
        'qty' => 'required|integer|min:1',
        'orderId'=>'required|exists:orders,id|min:1'
        ]);
        $orderItem = OrderItem::find($id);
        if(!$orderItem){
            return ApiResponse::error(__('crud.not_found'));
        }
        $product = Product::where('id', $orderItem->product_id)->select(['cost','price'])->first();
        $orderItem->update([
            'qty' => $data['qty'],
            'price' => $product->price,
            'cost'=> $product->cost,
        ]);
        return ApiResponse::success(__('crud.updated'));
    }

    public function destory(Request $request,$id)
    {
        $orderItem = OrderItem::find($id);
        if(!$orderItem){
            return ApiResponse::error(__('crud.not_found'));
        }
        $orderItem->delete();
        return ApiResponse::success(__('crud.deleted'));
    }


}
