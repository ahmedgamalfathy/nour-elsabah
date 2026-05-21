<?php

namespace App\Services\Inventory;

use App\Enums\Product\LimitedQuantity;
use App\Exceptions\InsufficientStockException;
use App\Models\Order\Order;
use App\Models\Product\Product;
use Illuminate\Support\Facades\DB;

/**
 * Coordinates all stock side effects for orders.
 *
 * Inventory is intentionally kept outside controllers and payment gateways.
 * The Order aggregate records whether inventory has already been deducted,
 * making stock operations idempotent and preventing duplicate decrements when
 * a cart moves through payment callbacks or admin status updates.
 */
class InventoryService
{
    /**
     * Validate and decrement stock for limited-quantity products exactly once.
     *
     * @throws InsufficientStockException
     */
    public function decrementStockIfNeeded(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->inventory_deducted) {
                return;
            }

            $lockedOrder->loadMissing('items.product');

            foreach ($lockedOrder->items as $item) {
                $product = $item->product;

                if (! $product || $product->is_limited_quantity !== LimitedQuantity::LIMITED) {
                    continue;
                }

                $affected = Product::whereKey($product->id)
                    ->where('quantity', '>=', $item->qty)
                    ->update([
                        'quantity' => DB::raw('quantity - ' . (float) $item->qty),
                    ]);

                if ($affected !== 1) {
                    $freshProduct = Product::find($product->id);

                    throw new InsufficientStockException(
                        $freshProduct?->name ?? $product->name,
                        (float) ($freshProduct?->quantity ?? 0)
                    );
                }
            }

            $lockedOrder->forceFill(['inventory_deducted' => true])->saveQuietly();
        });
    }

    /**
     * Restore previously deducted stock exactly once.
     *
     * Used when orders are cancelled or deleted. If inventory was never
     * deducted, the method is a no-op, which keeps guest carts and draft orders
     * safe to delete without inflating stock.
     */
    public function restoreStock(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $lockedOrder || ! $lockedOrder->inventory_deducted) {
                return;
            }

            $lockedOrder->loadMissing('items.product');

            foreach ($lockedOrder->items as $item) {
                $product = $item->product;

                if (! $product || $product->is_limited_quantity !== LimitedQuantity::LIMITED) {
                    continue;
                }

                Product::whereKey($product->id)->update([
                    'quantity' => DB::raw('quantity + ' . (float) $item->qty),
                ]);
            }

            $lockedOrder->forceFill(['inventory_deducted' => false])->saveQuietly();
        });
    }

    /**
     * Read-only availability check for carts and draft orders.
     *
     * This preserves user feedback while avoiding premature stock mutation.
     *
     * @throws InsufficientStockException
     */
    public function assertStockAvailable(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || $product->is_limited_quantity !== LimitedQuantity::LIMITED) {
                continue;
            }

            if ((float) $product->quantity < (float) $item->qty) {
                throw new InsufficientStockException($product->name, (float) $product->quantity);
            }
        }
    }
}
