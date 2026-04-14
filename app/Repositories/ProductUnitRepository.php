<?php

namespace App\Repositories;

use App\Models\ProductUnit;

class ProductUnitRepository
{
    public function all()
    {
        return ProductUnit::all();
    }
    public function find($id)
    {
        return ProductUnit::findOrFail($id);
    }
    public function create(array $data)
    {
        return ProductUnit::create($data);
    }
    public function update($id, array $data)
    {
        $productUnit = ProductUnit::findOrFail($id);
        $productUnit->update($data);
        return $productUnit;
    }
    public function delete($id)
    {
        $productUnit = ProductUnit::findOrFail($id);
        return $productUnit->delete();
    }
}
