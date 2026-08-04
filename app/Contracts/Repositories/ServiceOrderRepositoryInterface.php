<?php

namespace App\Contracts\Repositories;

interface ServiceOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStatus($status);

    public function findByMechanic($mechanicId);

    public function findByVehicle($vehicleId);

    public function findWithRelations($id);
}
