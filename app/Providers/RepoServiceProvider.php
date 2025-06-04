<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\TableAreas\TableAreaRepositoryInterface;
use App\Repositories\TableAreas\TableAreaRepository;

class RepoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Repositories\Branchs\BranchRepositoryInterface::class, \App\Repositories\Branchs\BranchRepository::class);
        $this->app->bind(TableAreaRepositoryInterface::class, TableAreaRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
