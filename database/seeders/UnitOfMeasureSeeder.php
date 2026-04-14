<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitOfMeasure;

class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        UnitOfMeasure::create([
            'tenant_id' => 1,
            'abbreviation' => 'EA',
            'name' => ['en' => 'Each', 'ar' => 'حبة'],
            'dimension_type' => 'count',
            'is_active' => true,
        ]);

        UnitOfMeasure::create([
            'tenant_id' => 1,
            'abbreviation' => 'KG',
            'name' => ['en' => 'Kilogram', 'ar' => 'كيلوغرام'],
            'dimension_type' => 'weight',
            'is_active' => true,
        ]);
    }
}
