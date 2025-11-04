<?php
namespace App\Services\Order;

use App\Models\Order\OrderItem;
use App\Models\Product\Product;

class OrderItemService
{
    public function allOrderItems()
    {
        $orderItems = OrderItem::get();
        return $orderItems;
    }

    public function editOrderItem($id)
    {
        $orderItem = OrderItem::with(['order', 'product'])->find($id);
        return $orderItem;
    }
    public function createOrderItem(array $data)
    {
        $product = Product::where('id', $data['productId'])->select(['cost','price'])->first();
        $orderItem = OrderItem::create([
            'order_id' => $data['orderId'],
            'product_id' => $data['productId'],
            'price' => $product->price,
            'cost'=> $product->cost,
            'qty' => $data['qty'],
        ]);
        return $orderItem;
    }
    public function updateOrderItem(int $id,array $data ){
        $orderItem = OrderItem::find($id);
        $product = Product::where('id', $orderItem->product_id)->select(['cost','price'])->first();
        $orderItem->update([
            'qty' => $data['qty'],
            'price' => $product->price,
            'cost'=> $product->cost,
        ]);
        return $orderItem;
    }
    public function deleteOrderItem(int $id)
    {
        $orderItem = OrderItem::find($id);
            $orderItem->delete();
    }
}
