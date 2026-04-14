<?php

namespace App\Repositories;

use App\Models\Tenant;

class TenantRepository
{
    public function all()
    {
        return Tenant::all();
    }
    public function find($id)
    {
        return Tenant::findOrFail($id);
    }
    public function create(array $data)
    {
        return Tenant::create($data);
    }
    public function update($id, array $data)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($data);
        return $tenant;
    }
    public function delete($id)
    {
        $tenant = Tenant::findOrFail($id);
        return $tenant->delete();
    }
}
