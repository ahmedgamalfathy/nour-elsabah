<?php

namespace App\Pipelines\Order;

use App\DTOs\Order\OrderCheckoutData;
use App\Enums\Product\LimitedQuantity;
use App\Exceptions\InsufficientStockException;
use App\Models\Product\Product;
use App\Traits\ValidatesOrderQuantity;
use Closure;
use Illuminate\Validation\ValidationException;

/**
 * Enforces product availability and selling-unit invariants before persistence.
 *
 * This stage is read-only: it validates product existence, min quantity,
 * quantity step, and limited stock using the Product model. Keeping this logic
 * here prevents the historical `$item->is_limited_quantity` bug because the
 * limited-stock decision is always read from the product relationship/model.
 */
class ValidateStockAndSteps
{
    use ValidatesOrderQuantity;

    public function handle(OrderCheckoutData $data, Closure $next): mixed
    {
        foreach ($data->items() as $index => $itemData) {
            $product = Product::with('unit')->find($itemData['productId'] ?? null);

            if (! $product) {
                throw ValidationException::withMessages([
                    "orderItems.$index.productId" => __('validation.exists', ['attribute' => 'product']),
                ]);
            }

            $qty = (float) ($itemData['qty'] ?? 0);

            if ($error = $this->validateItemQuantity($product, $qty)) {
                throw ValidationException::withMessages([
                    "orderItems.$index.qty" => $error,
                ]);
            }

            if ($product->is_limited_quantity === LimitedQuantity::LIMITED && (float) $product->quantity < $qty) {
                throw new InsufficientStockException($product->name, (float) $product->quantity);
            }
        }

        return $next($data);
    }
}
