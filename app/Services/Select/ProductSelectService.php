<?php
namespace App\Services\Select;
use App\Models\Product\Product;


class ProductSelectService {
    public function getAllProducts() {
        return Product::where('status', 1)->get(['id as value', 'name as label']);
    }
}