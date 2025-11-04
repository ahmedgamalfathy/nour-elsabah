<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Enums\IsMain;
use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use App\Models\Client\ClientEmail;
use App\Models\Client\ClientPhone;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Client\ClientAdrress;
use App\Enums\Product\LimitedQuantity;
use App\Services\Order\OrderItemService;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Resources\Order\Website\OrderResource;
use App\Http\Requests\Order\Website\CreateOrderRequest;

class OrderController extends Controller
{
    public $orderItemService;
    public function __construct(OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
    }

    public function store(CreateOrderRequest $createOrderRequest)
    {
        try {
            DB::beginTransaction();
            $data =$createOrderRequest->validated();
            $client=Client::create([
                'name'=>$data['name'],
                'note'=>$data['note'],
            ]); //name ,note ,phone ,countryCode ,email ,address ,streetNumber , city ,region
            $clientPhone = ClientPhone::create([
                'client_id' => $client->id,
                'phone' => $data['phone'],
                'country_code' => $data['countryCode'] ?? null,
                'is_main' =>  IsMain::PRIMARY->value,
            ]);
            $clientEmail = ClientEmail::create([
                'client_id' => $client->id,
                'email' => $data['email'],
                'is_main' => IsMain::PRIMARY->value ,
            ]);
            $clientAddress = ClientAdrress::create([
                'client_id' => $client->id,
                'address' => $data['address'],
                'street_number'=>$data['streetNumber']??null,
                'city'=>$data['city']??null,
                'region'=>$data['region']??null,
                'is_main' => IsMain::PRIMARY->value ,
            ]);
             ////////////////order create////////////////////
             $totalCost =0;
             $totalPrice = 0;
             $totalPriceAfterDiscount = 0;
             $order = Order::create([
                 'discount' =>0.00,
                 'discount_type' =>0,
                 'client_phone_id' => $clientPhone->id,
                 'client_email_id' => $clientEmail->id,
                 'client_address_id' =>$clientAddress->id,
                 'client_id' => $client->id,
                 'status' => 0,
             ]);
             $avilableQuantity = [];
             foreach ($data['orderItems'] as $itemData) {
                 $item= $this->orderItemService->createOrderItem([
                         'orderId' => $order->id,
                         ...$itemData
                     ]);
                     //&& $item->product->quantity < $item->qty
                 if($item->product->is_limited_quantity == LimitedQuantity::LIMITED ){
                     if ($item->product->quantity < $item->qty) {
                         $avilableQuantity[] = [
                             'productId' => $item->product->id,
                             'quantity' => $item->product->quantity,
                             'name' => $item->product->name
                         ];
                         return  $avilableQuantity;
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
                 'total_cost'=>$totalCost
             ]);
            DB::commit();
            // return ApiResponse::success([],__('crud.created'));
            return ApiResponse::success(new OrderResource($order));
    }catch (\Exception $e){
        DB::rollBack();
        return ApiResponse::error($e->getMessage());
     }
    }

}

