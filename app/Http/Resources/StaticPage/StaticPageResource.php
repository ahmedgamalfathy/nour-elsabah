<?php

namespace App\Http\Resources\StaticPage;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaticPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "title" => $this->title,
            "content" => $this->content,
            "updatedAt"=>$this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d') :""
        ];
    }
}
