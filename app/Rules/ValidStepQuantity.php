<?php

namespace App\Rules;

use App\Models\Product\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that the requested quantity satisfies:
 *  1. qty >= product.min_quantity
 *  2. qty is a multiple of product.quantity_step  (uses fmod for decimal safety)
 */
class ValidStepQuantity implements ValidationRule
{
    public function __construct(private readonly int $productId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $product = Product::with('unit')
            ->select(['id', 'min_quantity', 'quantity_step', 'unit_id'])
            ->find($this->productId);

        if (! $product) {
            $fail(__('validation.exists', ['attribute' => 'product']));
            return;
        }

        $qty  = (float) $value;
        $min  = (float) $product->min_quantity;
        $step = (float) $product->quantity_step;

        if ($qty < $min) {
            $fail(__('validation.custom.qty_below_minimum', [
                'min'  => $min,
                'unit' => $product->unit?->name ?? '',
            ]));
            return;
        }

        if ($step > 0) {
            $remainder  = fmod($qty, $step);
            $isMultiple = $remainder < 0.0001 || ($step - $remainder) < 0.0001;

            if (! $isMultiple) {
                $fail(__('validation.custom.invalid_step_quantity', [
                    'step' => $step,
                    'unit' => $product->unit?->name ?? '',
                ]));
            }
        }
    }
}
