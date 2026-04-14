<?php

namespace App\Repositories;

use App\Models\SerialNumber;

class SerialNumberRepository
{
    public function all()
    {
        return SerialNumber::all();
    }
    public function find($id)
    {
        return SerialNumber::findOrFail($id);
    }
    public function create(array $data)
    {
        return SerialNumber::create($data);
    }
    public function update($id, array $data)
    {
        $serialNumber = SerialNumber::findOrFail($id);
        $serialNumber->update($data);
        return $serialNumber;
    }
    public function delete($id)
    {
        $serialNumber = SerialNumber::findOrFail($id);
        return $serialNumber->delete();
    }
}
