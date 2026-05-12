<?php

namespace App\Http\Resources\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'unitId' => $this->id,
            'name'   => $this->name,
            'step'   => $this->step,
        ];
    }
}
