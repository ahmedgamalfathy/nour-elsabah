<?php

namespace App\Traits;

use App\Models\Product\Product;

/**
 * Shared quantity validation logic for both Dashboard and Website order flows.
 * Ensures requested qty respects the product's min_quantity and quantity_step.
 */
trait ValidatesOrderQuantity
{
    /**
     * Validate a requested quantity against the product's min_quantity and quantity_step.
     *
     * @return string|null  Error message, or null if valid.
     */
    protected function validateItemQuantity(Product $product, float $qty): ?string
    {
        $min  = (float) $product->min_quantity;
        $step = (float) $product->quantity_step;
        $unit = $product->unit?->name ?? '';

        if ($qty < $min) {
            return "الكمية يجب أن لا تقل عن {$min} {$unit}.";
        }

        if ($step > 0) {
            $remainder  = fmod($qty, $step);
            $isMultiple = $remainder < 0.0001 || ($step - $remainder) < 0.0001;

            if (! $isMultiple) {
                return "الكمية يجب أن تكون من مضاعفات {$step} {$unit}.";
            }
        }

        return null;
    }

    /**
     * Validate all orderItems in a data array.
     * Returns an array of errors keyed by index, or empty array if all valid.
     *
     * @param  array<int, array{productId: int, qty: float|int}>  $items
     * @return array<int, string>
     */
    protected function validateItemsQuantities(array $items): array
    {
        $errors = [];

        foreach ($items as $index => $itemData) {
            $product = Product::with('unit')
                ->select(['id', 'min_quantity', 'quantity_step', 'unit_id'])
                ->find($itemData['productId'] ?? null);

            if (! $product) {
                continue;
            }

            $error = $this->validateItemQuantity($product, (float) ($itemData['qty'] ?? 0));

            if ($error) {
                $errors[$index] = $error;
            }
        }

        return $errors;
    }
}
