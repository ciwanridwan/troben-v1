<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
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
        $this->app->singleton(PartnerRepository::class, function (Application $app) {
            return new PartnerRepository(fn () => $app->make(Request::class));
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
