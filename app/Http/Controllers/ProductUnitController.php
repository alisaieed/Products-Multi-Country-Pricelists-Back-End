<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductUnitService;

class ProductUnitController extends Controller
{
    protected $service;

    public function __construct(ProductUnitService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAllProductUnits());
    }

    public function show($id)
    {
        return response()->json($this->service->getProductUnitById($id));
    }

    public function store(Request $request)
    {
        $product = $this->service->createProductUnit($request->all());
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = $this->service->updateProductUnit($id, $request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $this->service->deleteProductUnit($id);
        return response()->json(null, 204);
    }
}
