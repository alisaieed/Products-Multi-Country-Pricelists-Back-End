<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'name' => 'SuperMart',
            'key' => 'supermart-key'
        ]);

        Tenant::create([
            'name' => 'PharmaPlus',
            'key' => 'pharmaplus-key'
]);
    }
}
