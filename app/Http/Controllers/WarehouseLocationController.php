<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseLocationService;

class WarehouseLocationController extends Controller
{
    protected $service;
    public function __construct(WarehouseLocationService $service) { $this->service = $service; }

    public function index() { return response()->json($this->service->getAll()); }
    public function show($id) { return response()->json($this->service->getById($id)); }
    public function store(Request $request) {
        $warehouseLocation = $this->service->create($request->all());
        return response()->json($warehouseLocation, 201);
    }
    public function update(Request $request, $id) {
        $warehouseLocation = $this->service->update($id, $request->all());
        return response()->json($warehouseLocation);
    }
    public function destroy($id) {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
