<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BatchService;

class BatchController extends Controller
{
    protected $service;
    public function __construct(BatchService $service) { $this->service = $service; }

    public function index() { return response()->json($this->service->getAll()); }
    public function show($id) { return response()->json($this->service->getById($id)); }
    public function store(Request $request) {
        $batch = $this->service->create($request->all());
        return response()->json($batch, 201);
    }
    public function update(Request $request, $id) {
        $this->service->update($id, $request->all());
        $batch = $this->service->getById($id);
        return response()->json($batch);
    }
    public function destroy($id) {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
