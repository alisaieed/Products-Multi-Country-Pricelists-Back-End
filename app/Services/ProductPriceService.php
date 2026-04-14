<?php

namespace App\Services;

use App\Repositories\ProductPriceRepository;

class ProductPriceService
{
    protected $repository;

    public function __construct(ProductPriceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getById($id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function getPriceForProductAndCountry($productId, $countryId)
    {
        return $this->repository->findByProductAndCountry($productId, $countryId);
    }
}
