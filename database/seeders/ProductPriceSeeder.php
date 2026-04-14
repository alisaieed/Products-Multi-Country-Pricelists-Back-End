<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductPrice;

class ProductPriceSeeder extends Seeder
{
    public function run(): void
    {
        ProductPrice::create([
            'product_id' => 1,
            'country_id' => 1, // Saudi Arabia
            'price' => 5.00,
        ]);

        ProductPrice::create([
            'product_id' => 1,
            'country_id' => 2, // Malaysia
            'price' => 4.50,
        ]);

        ProductPrice::create([
            'product_id' => 2,
            'country_id' => 3, // USA
            'price' => 2.00,
        ]);
    }
}
