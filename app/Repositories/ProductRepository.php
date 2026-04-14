<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function all()
    {
        return Product::with([
            'countries' => function ($query) {
                $query->select('countries.id', 'countries.name', 'countries.code', 'countries.currency');
            }
        ])->get()->map(fn ($product) => $this->formatProductWithCountries($product));
    }

    public function getProductsWithCountriesAndPricesByCategory($categoryId)
    {
        return Product::where('category_id', $categoryId)
            ->with(['prices.country']) // eager load prices and their country
            ->get();
    }

    public function getPriceByProductAndCountry($productId, $countryId)
    {
        return ProductPrice::where('product_id', $productId)
                           ->where('country_id', $countryId)
                           ->with('country') // eager load country for currency
                           ->first(['product_id', 'country_id', 'price']);
    }

    public function getProductByIdAndCountry($productId, $countryId)
    {
        $product = Product::where('id', $productId)
            ->whereHas('prices', function ($query) use ($countryId) {
                $query->where('country_id', $countryId);
            })
            ->with([
                'prices' => function ($query) use ($countryId) {
                    $query->where('country_id', $countryId)
                        ->with('country:id,currency');
                }
            ])
            ->first();

        if (!$product) {
            return null;
        }

        $priceRecord = $product->prices->first();
        $productArray = $product->toArray();

        $merged = array_merge($productArray, [
            'country_id' => data_get($priceRecord, 'country_id'),
            'price' => data_get($priceRecord, 'price'),
            'currency' => data_get($priceRecord, 'country.currency'),
        ]);

        unset($merged['prices']);

        return $merged;
    }

    public function getByCountryAndCategory($countryId, $categoryId = null)
    {
        $query = Product::whereHas('prices', function ($q) use ($countryId) {
            $q->where('country_id', $countryId);
        });

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->with([
            'prices' => function ($q) use ($countryId) {
                $q->where('country_id', $countryId)
                    ->with('country:id,currency');
            }
        ])->get()->map(function ($product) {
            $priceRecord = $product->prices->first();

            $productArray = is_object($product) && method_exists($product, 'toArray')
                ? $product->toArray()
                : (array) $product;

            $merged = array_merge($productArray, [
                'country_id' => data_get($priceRecord, 'country_id'),
                'price' => data_get($priceRecord, 'price'),
                'currency' => data_get($priceRecord, 'country.currency'),
            ]);

            // remove the original prices array
            unset($merged['prices']);

            return $merged;
        });
    }

    public function find($id)
    {
        $product = Product::with([
            'countries' => function ($query) {
                $query->select('countries.id', 'countries.name', 'countries.code', 'countries.currency');
            }
        ])->findOrFail($id);

        return $this->formatProductWithCountries($product);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $countries = $data['countries'] ?? [];
            unset($data['countries']);

            $product = Product::create($data);

            $this->syncProductCountries($product, $countries);

            return $this->formatProductWithCountries($product->fresh(['countries']));
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Product::findOrFail($id);
            $countries = $data['countries'] ?? null;
            unset($data['countries']);

            $product->update($data);

            if (is_array($countries)) {
                $this->syncProductCountries($product, $countries);
            }

            return $this->formatProductWithCountries($product->fresh(['countries']));
        });
    }

    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $product = Product::findOrFail($id);

            ProductPrice::where('product_id', $product->id)->delete();

            return $product->delete();
        });
    }

    public function getByCountry($countryId)
    {
        return Product::whereHas('prices', function ($query) use ($countryId) {
            $query->where('country_id', $countryId);
        })->get();
    }

    protected function syncProductCountries(Product $product, array $countries): void
    {
        ProductPrice::where('product_id', $product->id)->delete();

        $rows = collect($countries)
            ->filter(function ($country) {
                return isset($country['country_id']) && array_key_exists('price', $country);
            })
            ->map(function ($country) use ($product) {
                return [
                    'product_id' => $product->id,
                    'country_id' => $country['country_id'],
                    'price' => $country['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        if (!empty($rows)) {
            ProductPrice::insert($rows);
        }
    }

    protected function formatProductWithCountries(Product $product): array
    {
        $productArray = $product->toArray();

        $productArray['countries'] = collect($product->countries)->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'currency' => $country->currency,
                'country_id' => $country->id,
                'price' => data_get($country, 'pivot.price'),
            ];
        })->values()->all();

        return $productArray;
    }

}
