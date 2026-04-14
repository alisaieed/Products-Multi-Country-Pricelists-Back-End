<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function getProductsWithCountriesAndPricesByCategory($categoryId)
    {
        $products = $this->service->getProductsWithCountriesAndPricesByCategory($categoryId);

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this category'], 404);
        }

        return response()->json($products->map(function ($product) {
            return [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'countries'    => $product->prices->map(function ($price) {
                    return [
                        'country_id'   => $price->country->id,
                        'country_name' => $price->country->name,
                        'currency'     => $price->country->currency,
                        'price'        => $price->price,
                    ];
                }),
            ];
        }));
    }

    public function getPriceByProductAndCountry($productId, $countryId)
    {
        $priceRecord = $this->service->getPriceByProductAndCountry($productId, $countryId);

        if (!$priceRecord) {
            return response()->json(['message' => 'Price not found'], 404);
        }

        return response()->json([
            'product_id' => $priceRecord->product_id,
            'country_id' => $priceRecord->country_id,
            'price'      => $priceRecord->price,
            'currency'   => $priceRecord->country->currency ?? 'N/A'
        ]);
    }

    public function getProductByIdAndCountry($productId, $countryId)
    {
        $product = $this->service->getProductByIdAndCountry($productId, $countryId);

        if (!$product) {
            return response()->json(['message' => 'Product or price not found for this country'], 404);
        }

        return response()->json($product);
    }

    public function getByCountryAndCategory($countryId, $categoryId = null)
    {
        $products = $this->service->getProductsByCountryAndCategory($countryId, $categoryId);
        return response()->json($products);
    }
    public function index()
    {
        return response()->json($this->service->getAllProducts());
    }

    public function show($id)
    {
        return response()->json($this->service->getProductById($id));
    }

    public function store(Request $request)
    {
        $product = $this->service->createProduct($request->all());
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = $this->service->updateProduct($id, $request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $this->service->deleteProduct($id);
        return response()->json(null, 204);
    }

    // public function getByCountry($countryId)
    // {
    //     $products = $this->service->getProductsByCountry($countryId);
    //     return response()->json($products);
    // }

}
