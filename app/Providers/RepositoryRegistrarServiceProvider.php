<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Supports\Repositories\PartnerRepository;

class RepositoryRegistrarServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PartnerRepository::class, function () {
            return new PartnerRepository($this->app);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
