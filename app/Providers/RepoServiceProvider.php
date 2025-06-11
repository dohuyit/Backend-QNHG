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
        $this->app->bind(\App\Repositories\Categories\CategoryRepositoryInterface::class, \App\Repositories\Categories\CategoryRepository::class);
        $this->app->bind(\App\Repositories\Users\UserRepositoryInterface::class, \App\Repositories\Users\UserRepository::class);
        $this->app->bind(\App\Repositories\Auth\AuthVerifyTokenRepositoryInterface::class, \App\Repositories\Auth\AuthVerifyTokenRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
