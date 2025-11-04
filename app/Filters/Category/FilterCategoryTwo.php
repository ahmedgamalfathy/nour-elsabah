<?php
namespace App\Filters\Category;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

use function PHPUnit\Framework\isNull;

class FilterCategoryTwo implements Filter
{
    public function __invoke(Builder $query, $value, string $property ): Builder
    {
        $parentId = request()->input('filter.parentId');
        return $query->where(function ($query) use ($value, $parentId) {
            $query->where('name', 'like', '%' . $value . '%');
            if ($parentId !== null && $parentId !== '') {
                $query->where('parent_id', $parentId);
            } else {
                $query->whereNull('parent_id');
            }
        });
    }
}
?>