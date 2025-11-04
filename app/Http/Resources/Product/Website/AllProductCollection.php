<?php

namespace App\Http\Resources\Product\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Product\Website\AllProductResource;

class AllProductCollection extends ResourceCollection
{

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    private $pagination;

    public function __construct($resource)
    {
        $this->pagination = [
            'total' => $resource->total(),
            'count' => $resource->count(),
            'perPage' => $resource->perPage(),
            'currentPage' => $resource->currentPage(),
            'totalPages' => $resource->lastPage()
        ];
        parent::__construct($resource->getCollection());
       }
    public function toArray(Request $request): array
    {
        return [
            'products' => AllProductResource::collection($this->collection),
            'pagination' => $this->pagination
           ];
    }//
}
