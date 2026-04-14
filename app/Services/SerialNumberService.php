<?php

namespace App\Services;

use App\Repositories\SerialNumberRepository;

class SerialNumberService
{
    protected $repository;

    public function __construct(SerialNumberRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllProductSerialNumbers()
    {
        return $this->repository->all();
    }

    public function getProductSerialNumberById($id)
    {
        return $this->repository->find($id);
    }

    public function createProductSerialNumber(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateProductSerialNumber($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteProductSerialNumber($id)
    {
        return $this->repository->delete($id);
    }
}
