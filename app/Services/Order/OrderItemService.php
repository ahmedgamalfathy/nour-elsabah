<?php

namespace App\Services\Order;

use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Traits\ValidatesOrderQuantity;

class OrderItemService
{
    use ValidatesOrderQuantity;

    public function allOrderItems()
    {
        return OrderItem::get();
    }

    public function editOrderItem(int $id): OrderItem
    {
        return OrderItem::with(['order', 'product'])->findOrFail($id);
    }

    public function createOrderItem(array $data): OrderItem
    {
        $product = Product::with('unit')
            ->select(['id', 'cost', 'price', 'min_quantity', 'quantity_step', 'unit_id'])
            ->findOrFail($data['productId']);

        // Validate step & min quantity — throws if invalid
        if ($error = $this->validateItemQuantity($product, (float) $data['qty'])) {
            throw new \InvalidArgumentException($error);
        }

        return OrderItem::create([
            'order_id'   => $data['orderId'],
            'product_id' => $product->id,
            // unit price × decimal qty = line total (calculated at order level)
            'price'      => $product->price,
            'cost'       => $product->cost,
            'qty'        => $data['qty'],
        ]);
    }

    public function updateOrderItem(int $id, array $data): OrderItem
    {
        $orderItem = OrderItem::findOrFail($id);
        $product   = Product::with('unit')
            ->select(['id', 'cost', 'price', 'min_quantity', 'quantity_step', 'unit_id'])
            ->findOrFail($orderItem->product_id);

        // Validate step & min quantity — throws if invalid
        if ($error = $this->validateItemQuantity($product, (float) $data['qty'])) {
            throw new \InvalidArgumentException($error);
        }

        $orderItem->update([
            'qty'   => $data['qty'],
            'price' => $product->price,
            'cost'  => $product->cost,
        ]);

        return $orderItem->fresh();
    }

    public function deleteOrderItem(int $id): void
    {
        OrderItem::findOrFail($id)->delete();
    }
}
