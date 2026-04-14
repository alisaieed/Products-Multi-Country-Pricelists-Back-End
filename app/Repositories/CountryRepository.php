<?php

namespace App\Repositories;

use App\Models\Country;

class CountryRepository
{
    public function all() { return Country::all(); }
    public function find($id) { return Country::findOrFail($id); }
    public function create(array $data) { return Country::create($data); }
    public function update($id, array $data) {
        $country = Country::findOrFail($id);
        $country->update($data);
        return $country;
    }
    public function delete($id) {
        $country = Country::findOrFail($id);
        return $country->delete();
    }
}
