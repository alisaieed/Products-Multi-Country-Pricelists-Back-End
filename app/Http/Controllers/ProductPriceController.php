<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductPriceService;

class ProductPriceController extends Controller
{
    protected $service;

    public function __construct(ProductPriceService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAll());
    }

    public function show($id)
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request)
    {
        $price = $this->service->create($request->all());
        return response()->json($price, 201);
    }

    public function update(Request $request, $id)
    {
        $price = $this->service->update($id, $request->all());
        return response()->json($price);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }

    /**
     * Custom endpoint: get price for product in a specific country.
     */
    public function getPriceByProductAndCountry($productId, $countryId)
    {
        $price = $this->service->getPriceForProductAndCountry($productId, $countryId);
        return response()->json($price);
    }
}
