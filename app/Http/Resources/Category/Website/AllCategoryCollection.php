<?php

namespace App\Http\Resources\Category\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Category\Website\AllCategoryResource;

class AllCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
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

         $resource = $resource->getCollection();

         parent::__construct($resource);
     }


    public function toArray(Request $request): array
    {

        return [
            'categories' => AllCategoryResource::collection(($this->collection)->values()->all()),
            'pagination' => $this->pagination
        ];

    }
}
