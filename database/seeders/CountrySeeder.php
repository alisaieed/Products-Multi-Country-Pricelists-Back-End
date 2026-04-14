<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::create(['name' => 'Saudi Arabia', 'code' => 'SA', 'currency' => 'SAR']);
        Country::create(['name' => 'Malaysia', 'code' => 'MY', 'currency' => 'MYR']);
        Country::create(['name' => 'United States', 'code' => 'US', 'currency' => 'USD']);
    }
}
