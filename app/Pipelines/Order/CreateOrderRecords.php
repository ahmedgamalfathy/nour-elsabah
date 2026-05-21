<?php

namespace App\Pipelines\Order;

use App\DTOs\Order\OrderCheckoutData;
use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use Closure;

/**
 * Persists the Order aggregate and its line items.
 *
 * The stage writes records only after client resolution and product validation
 * have succeeded. Items are bulk inserted for efficiency; the following
 * promotion stage explicitly recalculates totals because bulk inserts do not
 * fire Eloquent model events.
 */
class CreateOrderRecords
{
    public function handle(OrderCheckoutData $data, Closure $next): mixed
    {
        $order = Order::create([
            'discount' => $data->inputData['discount'] ?? 0,
            'discount_type' => $data->inputData['discountType'] ?? DiscountType::NO_DISCOUNT->value,
            'client_phone_id' => $data->clientPhoneId ?? $data->inputData['clientPhoneId'] ?? null,
            'client_email_id' => $data->clientEmailId ?? $data->inputData['clientEmailId'] ?? null,
            'client_address_id' => $data->clientAddressId ?? $data->inputData['clientAddressId'] ?? null,
            'client_id' => $data->clientId,
            'status' => $data->isGuestCheckout() ? OrderStatus::DRAFT->value : OrderStatus::IN_CART->value,
        ]);

        $productIds = collect($data->items())->pluck('productId')->all();
        $products = Product::whereIn('id', $productIds)
            ->get(['id', 'price', 'cost'])
            ->keyBy('id');

        $now = now();
        $rows = collect($data->items())->map(function (array $itemData) use ($order, $products, $now): array {
            $product = $products->get($itemData['productId']);

            return [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'cost' => $product->cost,
                'qty' => $itemData['qty'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        OrderItem::insert($rows);

        $data->order = $order;

        return $next($data);
    }
}
