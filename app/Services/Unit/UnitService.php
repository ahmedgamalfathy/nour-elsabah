<?php

namespace App\Services\Unit;

use App\Models\Unit\Unit;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UnitService
{
    public function allUnits()
    {
        return Unit::orderBy('name')->get();
    }

    public function findUnit(int $id): Unit
    {
        return Unit::findOrFail($id);
    }

    public function createUnit(array $data): Unit
    {
        return Unit::create([
            'name' => $data['name'],
            'step' => $data['step'],
        ]);
    }

    public function updateUnit(int $id, array $data): Unit
    {
        $unit = Unit::findOrFail($id);

        $unit->update([
            'name' => $data['name'],
            'step' => $data['step'],
        ]);

        return $unit;
    }

    public function deleteUnit(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
    }
}
