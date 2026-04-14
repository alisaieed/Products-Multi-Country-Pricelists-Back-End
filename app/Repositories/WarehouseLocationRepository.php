<?php

namespace App\Repositories;

use App\Models\WarehouseLocation;

class WarehouseLocationRepository
{
    public function all()
    {
        return WarehouseLocation::all();
    }
    public function find($id)
    {
        return WarehouseLocation::findOrFail($id);
    }
    public function create(array $data)
    {
        return WarehouseLocation::create($data);
    }
    public function update($id, array $data)
    {
        $warehouseLocation = WarehouseLocation::findOrFail($id);
        $warehouseLocation->update($data);
        return $warehouseLocation;
    }
    public function delete($id)
    {
        $warehouseLocation = WarehouseLocation::findOrFail($id);
        return $warehouseLocation->delete();
    }
}
