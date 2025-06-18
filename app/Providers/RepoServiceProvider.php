<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Repositories\Customers\CustomerRepositoryInterface::class, \App\Repositories\Customers\CustomerRepository::class);
        $this->app->bind(\App\Repositories\TableArea\TableAreaRepositoryInterface::class, \App\Repositories\TableArea\TableAreaRepository::class);
        $this->app->bind(\App\Repositories\Table\TableRepositoryInterface::class, \App\Repositories\Table\TableRepository::class);
        $this->app->bind(\App\Repositories\Categories\CategoryRepositoryInterface::class, \App\Repositories\Categories\CategoryRepository::class);
        $this->app->bind(\App\Repositories\Users\UserRepositoryInterface::class, \App\Repositories\Users\UserRepository::class);
        $this->app->bind(\App\Repositories\Auth\AuthVerifyTokenRepositoryInterface::class, \App\Repositories\Auth\AuthVerifyTokenRepository::class);
        $this->app->bind(\App\Repositories\Order\OrderRepositoryInterface::class, \App\Repositories\Order\OrderRepository::class);
        $this->app->bind(\App\Repositories\Dishes\DishRepositoryInterface::class, \App\Repositories\Dishes\DishRepository::class);
        $this->app->bind(\App\Repositories\Combos\ComboRepositoryInterface::class, \App\Repositories\Combos\ComboRepository::class);
        $this->app->bind(\App\Repositories\ComboItems\ComboItemRepositoryInterface::class, \App\Repositories\ComboItems\ComboItemRepository::class);
        $this->app->bind(\App\Repositories\Reservations\ReservationRepositoryInterface::class, \App\Repositories\Reservations\ReservationRepository::class);

        $this->app->bind(\App\Repositories\Role\RoleRepositoryInterface::class, \App\Repositories\Role\RoleRepository::class);
        $this->app->bind(\App\Repositories\PermissionGroup\PermissionGroupRepositoryInterface::class, \App\Repositories\PermissionGroup\PermissionGroupRepository::class);
        $this->app->bind(\App\Repositories\Permission\PermissionRepositoryInterface::class, \App\Repositories\Permission\PermissionRepository::class);
        $this->app->bind(\App\Repositories\UserRole\UserRoleRepositoryInterface::class, \App\Repositories\UserRole\UserRoleRepository::class);
        $this->app->bind(\App\Repositories\RolePermission\RolePermissionRepositoryInterface::class, \App\Repositories\RolePermission\RolePermissionRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
