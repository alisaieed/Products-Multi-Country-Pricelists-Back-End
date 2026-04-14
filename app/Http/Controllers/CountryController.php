<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CountryService;

class CountryController extends Controller
{
    protected $service;
    public function __construct(CountryService $service) { $this->service = $service; }

    public function index() { return response()->json($this->service->getAll()); }
    public function show($id) { return response()->json($this->service->getById($id)); }
    public function store(Request $request) {
        $country = $this->service->create($request->all());
        return response()->json($country, 201);
    }
    public function update(Request $request, $id) {
        $this->service->update($id, $request->all());
        $country = $this->service->getById($id);
        return response()->json($country);
    }
    public function destroy($id) {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
