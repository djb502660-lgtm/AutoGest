<?php

namespace App\Contracts\Repositories;

interface ServicePhotoRepositoryInterface
{
    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function findByServiceOrder($serviceOrderId);

    public function findByServiceOrderAndType($serviceOrderId, $type);

    public function where($column, $operator, $value = null);
}
