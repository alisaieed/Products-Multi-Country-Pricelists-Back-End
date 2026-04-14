<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $service;
    public function __construct(CategoryService $service) { $this->service = $service; }

    public function index() { return response()->json($this->service->getAll()); }
    public function show($id) { return response()->json($this->service->getById($id)); }
    public function store(Request $request) {
        $category = $this->service->create($request->all());
        return response()->json($category, 201);
    }
    public function update(Request $request, $id) {
        $category = $this->service->update($id, $request->all());
        return response()->json($category);
    }
    public function destroy($id) {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
