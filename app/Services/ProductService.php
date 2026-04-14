<?php

namespace App\Services;

use App\Repositories\ProductRepository;
class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPriceByProductAndCountry($productId, $countryId)
    {
        return $this->repository->getPriceByProductAndCountry($productId, $countryId);
    }

    public function getProductByIdAndCountry($productId, $countryId)
    {
        return $this->repository->getProductByIdAndCountry($productId, $countryId);
    }

    public function getProductsByCountryAndCategory($countryId, $categoryId = null)
    {
        return $this->repository->getByCountryAndCategory($countryId, $categoryId);
    }

    public function getAllProducts()
    {
        return $this->repository->all();
    }

    public function getProductById($id)
    {
        return $this->repository->find($id);
    }

    public function createProduct(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateProduct($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteProduct($id)
    {
        return $this->repository->delete($id);
    }

    public function getProductsByCountry($countryId)
    {
        return $this->repository->getByCountry($countryId);
    }

}
