<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TenantService;

class TenantController extends Controller
{
    protected $service;
    public function __construct(TenantService $service) { $this->service = $service; }

    public function index() { return response()->json($this->service->getAll()); }
    public function show($id) { return response()->json($this->service->getById($id)); }
    public function store(Request $request) {
        $tenant = $this->service->create($request->all());
        return response()->json($tenant, 201);
    }
    public function update(Request $request, $id) {
        $tenant = $this->service->update($id, $request->all());
        return response()->json($tenant);
    }
    public function destroy($id) {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
