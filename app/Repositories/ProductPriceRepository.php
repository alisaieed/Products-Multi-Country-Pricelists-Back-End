<?php

namespace App\Repositories;

use App\Models\ProductPrice;

class ProductPriceRepository
{
    public function all()
    {
        return ProductPrice::all();
    }

    public function find($id)
    {
        return ProductPrice::findOrFail($id);
    }

    public function create(array $data)
    {
        return ProductPrice::create($data);
    }

    public function update($id, array $data)
    {
        $price = ProductPrice::findOrFail($id);
        $price->update($data);
        return $price;
    }

    public function delete($id)
    {
        $price = ProductPrice::findOrFail($id);
        return $price->delete();
    }

    /**
     * Find price by product and country.
     */
    public function findByProductAndCountry($productId, $countryId)
    {
        return ProductPrice::where('product_id', $productId)
                           ->where('country_id', $countryId)
                           ->first();
    }
}
