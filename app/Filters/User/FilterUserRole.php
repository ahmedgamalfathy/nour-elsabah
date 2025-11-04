<?php

namespace App\Filters\User;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterUserRole implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $query->whereHas('roles', function ($query) use ($value) {
            $query->where('id', $value);
        });
        return $query;
    }
}
