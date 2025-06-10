<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\TableArea\TableAreaRepositoryInterface;
use App\Repositories\TableArea\TableAreaRepository;

class RepoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Repositories\Customers\CustomerRepositoryInterface::class, \App\Repositories\Customers\CustomerRepository::class);
        $this->app->bind(TableAreaRepositoryInterface::class, TableAreaRepositoryInterface::class);
        $this->app->bind(TableAreaRepositoryInterface::class, TableAreaRepository::class);

        $this->app->bind(\App\Repositories\Categories\CategoryRepositoryInterface::class, \App\Repositories\Categories\CategoryRepository::class);
        $this->app->bind(\App\Repositories\Users\UserRepositoryInterface::class, \App\Repositories\Users\UserRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
