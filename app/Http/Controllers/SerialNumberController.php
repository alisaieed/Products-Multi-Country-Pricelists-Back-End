<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SerialNumberService;

class SerialNumberController extends Controller
{
    protected $service;

    public function __construct(SerialNumberService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAllProductSerialNumbers());
    }

    public function show($id)
    {
        return response()->json($this->service->getProductSerialNumberById($id));
    }

    public function store(Request $request)
    {
        $product = $this->service->createProductSerialNumber($request->all());
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = $this->service->updateProductSerialNumber($id, $request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $this->service->deleteProductSerialNumber($id);
        return response()->json(null, 204);
    }
}
