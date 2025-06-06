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
        $this->app->bind(\App\Repositories\Branchs\BranchRepositoryInterface::class, \App\Repositories\Branchs\BranchRepository::class);
        $this->app->bind(\App\Repositories\Categories\CategoryRepositoryInterface::class, \App\Repositories\Categories\CategoryRepository::class);
        $this->app->bind(\App\Repositories\Dishes\DishRepositoryInterface::class, \App\Repositories\Dishes\DishRepository::class);

        
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
