<?php

namespace App\Providers;

use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Contracts\Repositories\MaintenanceRepositoryInterface;
use App\Contracts\Repositories\ServiceOrderRepositoryInterface;
use App\Contracts\Repositories\ServicePhotoRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\Repositories\Eloquent\MaintenanceRepository;
use App\Repositories\Eloquent\ServiceOrderRepository;
use App\Repositories\Eloquent\ServicePhotoRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VehicleRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(BaseRepositoryInterface::class, function ($app) {
            // This will be implemented by concrete repositories
        });

        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        $this->app->bind(ServiceOrderRepositoryInterface::class, ServiceOrderRepository::class);
        $this->app->bind(MaintenanceRepositoryInterface::class, MaintenanceRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ServicePhotoRepositoryInterface::class, ServicePhotoRepository::class);
    }

    public function boot()
    {
        //
    }
}
