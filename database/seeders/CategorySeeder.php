<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create([
            'tenant_id' => 1,
            'name' => ['en' => 'Beverages', 'ar' => 'مشروبات'],
            'description' => ['en' => 'Drinks and refreshments'],
            'code' => 'BEV',
            'is_active' => true,
        ]);

        Category::create([
            'tenant_id' => 1,
            'name' => ['en' => 'Snacks', 'ar' => 'وجبات خفيفة'],
            'description' => ['en' => 'Packaged snack items'],
            'code' => 'SNK',
            'is_active' => true,
        ]);
    }
}
