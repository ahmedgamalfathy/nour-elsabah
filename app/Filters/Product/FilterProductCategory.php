<?php
namespace App\Filters\Product;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterProductCategory implements Filter {
    public function __invoke(Builder $query, $value, string $property)
    {
        // Query Builder already handles the array conversion
        return $query->where(function ($query) use ($value) {
            $query->whereIn('category_id', (array) $value)
                  ->orWhereIn('sub_category_id', (array) $value);
        });
    }
}
