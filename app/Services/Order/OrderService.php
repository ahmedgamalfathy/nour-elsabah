<?php
namespace App\Services\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Enums\Product\LimitedQuantity;
use App\Exceptions\InsufficientStockException;
use App\Models\Order\Order;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
class OrderService
{
    protected $orderItemService;
    public function __construct( OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
    }
    public function allOrders(){
        $orders = QueryBuilder::for(Order::class)
        ->allowedFilters([
            'number',
            AllowedFilter::exact('clientId', 'client_id'),
            'status',
        ])
        ->orderByDesc('created_at')
        ->get();
        return $orders;
    }
    public function editOrder(int $id){
        $order= Order::with(['items','client','clientAddress','clientPhone','clientEmail'])->find($id);
        if(!$order){
            throw new ModelNotFoundException();
        }
        return $order;
    }

    public function createOrder(array $data){
        $totalCost =0;
        $totalPrice = 0;
        $totalPriceAfterDiscount = 0;

        $order = Order::create([
            'discount' => $data['discount']??null,
            'discount_type' => DiscountType::from($data['discountType'])->value,
            'client_phone_id' => $data['clientPhoneId'],
            'client_email_id' => $data['clientEmailId'],
            'client_address_id' => $data['clientAddressId'],
            'client_id' => $data['clientId'],
            'status' => OrderStatus::from($data['status'])->value,
        ]);

        $availableQuantity = [];
        foreach ($data['orderItems'] as $itemData) {
            $item= $this->orderItemService->createOrderItem([
                    'orderId' => $order->id,
                    ...$itemData
                ]);

            if($item->product->is_limited_quantity == LimitedQuantity::LIMITED){
                if ($item->product->quantity < $item->qty || $item->product->quantity == 0) {
                    $availableQuantity[] = [
                        'productId' => $item->product->id,
                        'quantity' => $item->product->quantity,
                        'name' => $item->product->name
                    ];
                    $order->delete();
                    return [
                        'availableQuantity' => $availableQuantity
                    ];
                }
                $item->product->decrement('quantity', $item->qty);
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
    return $order;

    }
    public function updateOrder(int $id,array $data){
        $order = Order::where('id', $id)->lockForUpdate()->first();
        $order->discount = $data['discount']??null;
        $order->discount_type = DiscountType::from($data['discountType'])->value;
        $order->client_phone_id = $data['clientPhoneId']??null;
        $order->client_email_id = $data['clientEmailId']??null;
        $order->client_address_id = $data['clientAddressId']??null;
        $order->client_id = $data['clientId'];
        $order->status = OrderStatus::from($data['status'])->value;
        $order->save();

        $totalCost=0;
        $totalPrice = 0;
        $totalPriceAfterDiscount = 0;
        foreach ($data['orderItems'] as $itemData) {
            if($itemData['actionStatus'] ==='update'){
                $itemOldQty = $this->orderItemService->editOrderItem($itemData['orderItemId'])->qty;

                $item= $this->orderItemService->updateOrderItem($itemData['orderItemId'],[
                    'orderId' => $order->id,
                    ...$itemData
                ]);

                if( $item->product->is_limited_quantity == LimitedQuantity::LIMITED){
                    $item->product->increment('quantity', $itemOldQty);
                    if ($item->product->quantity < $item->qty || $item->product->quantity == 0) {
                        $availableQuantity[] = [
                            'productId' => $item->product->id,
                            'quantity' => $item->product->quantity,
                            'name' => $item->product->name
                        ];
                        return [
                            'availableQuantity' => $availableQuantity
                        ];
                    }
                    $item->product->decrement('quantity', $item->qty);
                }

                $totalPrice += $item->price * $item->qty;
                $totalCost +=  $item->cost*$item->qty;
            }
            if($itemData['actionStatus'] ==='delete'){
                $item = $this->orderItemService->editOrderItem($itemData['orderItemId']);
                if($item->product->is_limited_quantity == LimitedQuantity::LIMITED){
                    $item->product->increment('quantity', $item->qty);
                }
                $this->orderItemService->deleteOrderItem($itemData['orderItemId']);
            }
            if($itemData['actionStatus'] ==='create'){
                    $item= $this->orderItemService->createOrderItem([
                        'orderId' => $order->id,
                        ...$itemData
                    ]);

                    if( $item->product->is_limited_quantity == LimitedQuantity::LIMITED){
                        if ($item->product->quantity < $item->qty || $item->product->quantity == 0) {
                            $availableQuantity[] = [
                                'productId' => $item->product->id,
                                'quantity' => $item->product->quantity,
                                'name' => $item->product->name
                            ];
                            return [
                                'availableQuantity' => $availableQuantity
                            ];
                        }
                        $item->product->decrement('quantity', $item->qty);
                    }

                    $totalPrice += $item->price * $item->qty;
                    $totalCost += $item->cost*$item->qty;
            }
            if($itemData['actionStatus'] ==''){
                $item= $this->orderItemService->editOrderItem($itemData['orderItemId']);
                $totalPrice += $item->price * $item->qty;
                $totalCost += $item->cost * $item->qty;
            }
        }
        if ($order->discount_type == DiscountType::PERCENTAGE) {
            $totalPriceAfterDiscount = $totalPrice - ($totalPrice * ($data['discount'] / 100));
        } elseif ($order->discount_type == DiscountType::FIXCED) {
            $totalPriceAfterDiscount = $totalPrice - $data['discount'];
        }elseif($order->discount_type == DiscountType::NO_DISCOUNT){
            $totalPriceAfterDiscount = $totalPrice;
        }
        $order->price_after_discount = $totalPriceAfterDiscount;
        $order->price = $totalPrice;
        $order->total_cost = $totalCost;
        $order->save();
        return $order;

    }
    public function deleteOrder(int $id){
            $order = Order::find($id);
            if(!$order){
                throw new ModelNotFoundException();
            }
            $order->delete();
    }

}
