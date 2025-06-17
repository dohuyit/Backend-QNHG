<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\TableAreaTemplates\TableAreaTemplateRepositoryInterface;
use App\Repositories\TableAreaTemplates\TableAreaTemplateRepository;
use App\Repositories\TableAreas\TableAreaRepositoryInterface;
use App\Repositories\TableAreas\TableAreaRepository;

class RepoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Repositories\Customers\CustomerRepositoryInterface::class, \App\Repositories\Customers\CustomerRepository::class);
        $this->app->bind(TableAreaTemplateRepositoryInterface::class, TableAreaTemplateRepository::class);
        $this->app->bind(TableAreaRepositoryInterface::class, TableAreaRepository::class);
        $this->app->bind(\App\Repositories\Categories\CategoryRepositoryInterface::class, \App\Repositories\Categories\CategoryRepository::class);
        $this->app->bind(\App\Repositories\Users\UserRepositoryInterface::class, \App\Repositories\Users\UserRepository::class);
        $this->app->bind(\App\Repositories\Auth\AuthVerifyTokenRepositoryInterface::class, \App\Repositories\Auth\AuthVerifyTokenRepository::class);
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
