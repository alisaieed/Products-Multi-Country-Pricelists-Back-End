<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function all() { return Category::all(); }
    public function find($id) { return Category::findOrFail($id); }
    public function create(array $data) { return Category::create($data); }
    public function update($id, array $data) {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }
    public function delete($id) {
        $category = Category::findOrFail($id);
        return $category->delete();
    }
}
