<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ServicePhotoRepositoryInterface;
use App\Models\ServicePhoto;

class ServicePhotoRepository implements ServicePhotoRepositoryInterface
{
    public function find($id)
    {
        return ServicePhoto::find($id);
    }

    public function create(array $data)
    {
        return ServicePhoto::create($data);
    }

    public function update($id, array $data)
    {
        $photo = $this->find($id);
        if ($photo) {
            $photo->update($data);

            return $photo;
        }

        return null;
    }

    public function delete($id)
    {
        $photo = $this->find($id);
        if ($photo) {
            return $photo->delete();
        }

        return false;
    }

    public function findByServiceOrder($serviceOrderId)
    {
        return ServicePhoto::where('service_order_id', $serviceOrderId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByServiceOrderAndType($serviceOrderId, $type)
    {
        return ServicePhoto::where('service_order_id', $serviceOrderId)
            ->where('type', $type)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function where($column, $operator, $value = null)
    {
        return ServicePhoto::where($column, $operator, $value);
    }
}
