<?php

namespace App\Services;

use App\Repositories\ProductUnitRepository;

class ProductUnitService
{
    protected $repository;

    public function __construct(ProductUnitRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllProductUnits()
    {
        return $this->repository->all();
    }

    public function getProductUnitById($id)
    {
        return $this->repository->find($id);
    }

    public function createProductUnit(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateProductUnit($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteProductUnit($id)
    {
        return $this->repository->delete($id);
    }
}
