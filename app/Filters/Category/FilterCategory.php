<?php

namespace App\Filters\Category;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterCategory implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($query) use ($value) {
            $query->where('name', 'like', '%' . $value . '%') // Search in main categories
                ->orWhereHas('subCategories', function ($subQuery) use ($value) {
                    $subQuery->where('name', 'like', '%' . $value . '%'); // Search in subcategories
                });
        });
    }
}
