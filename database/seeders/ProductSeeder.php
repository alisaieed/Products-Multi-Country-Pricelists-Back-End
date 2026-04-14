<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'tenant_id' => 1,
            'category_id' => 1,
            'sub_category_id' => 2,
            'sku' => 'SKU-10001',
            'barcode' => '1234567890123',
            'name' => ['en' => 'Mineral Water 1L', 'ar' => 'مياه معدنية 1 لتر'],
            'description' => ['en' => 'Pure bottled water'],
            'units_of_measure_id' => 2, // KG
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => 1,
            'category_id' => 2,
            'sub_category_id' => 2,
            'sku' => 'SKU-10002',
            'barcode' => '9876543210987',
            'name' => ['en' => 'Potato Chips 50g', 'ar' => 'رقائق بطاطس 50 جرام'],
            'description' => ['en' => 'Crunchy salted chips'],
            'units_of_measure_id' => 1, // EA
            'is_active' => true,
        ]);
    }
}
