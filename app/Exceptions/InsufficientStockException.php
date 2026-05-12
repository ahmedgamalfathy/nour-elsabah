<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly string $productName,
        public readonly float  $availableQuantity = 0
    ) {
        parent::__construct(
            "الكمية غير كافية للمنتج: {$productName}. الكمية المتاحة: {$availableQuantity}"
        );
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'product' => [
                'name'              => $this->productName,
                'availableQuantity' => $this->availableQuantity,
            ],
        ], 422);
    }
}
