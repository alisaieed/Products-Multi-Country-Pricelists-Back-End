<?php

namespace App\Repositories;

use App\Models\UnitOfMeasure;

class UnitOfMeasureRepository
{
    public function all()
    {
        return UnitOfMeasure::all();
    }
    public function find($id)
    {
        return UnitOfMeasure::findOrFail($id);
    }
    public function create(array $data)
    {
        return UnitOfMeasure::create($data);
    }
    public function update($id, array $data)
    {
        $unitOfMeasure = UnitOfMeasure::findOrFail($id);
        $unitOfMeasure->update($data);
        return $unitOfMeasure;
    }
    public function delete($id)
    {
        $unitOfMeasure = UnitOfMeasure::findOrFail($id);
        return $unitOfMeasure->delete();
    }
}
