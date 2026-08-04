<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByRole($role)
    {
        return $this->model->where('role', $role)->get();
    }

    public function findActiveUsers()
    {
        return $this->model->where('status', 'activo')->get();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findWithVehicles($id)
    {
        return $this->model->with('vehicles')->find($id);
    }

    public function count()
    {
        return $this->model->count();
    }
}
