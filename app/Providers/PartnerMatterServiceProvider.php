<?php

namespace App\Providers;

use App\Supports\Repositories\PartnerRepository;
use Illuminate\Support\ServiceProvider;

class PartnerMatterServiceProvider extends ServiceProvider
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
