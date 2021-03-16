<?php

namespace App\Providers;

use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackageUpdated;
use App\Listeners\Packages\GeneratePackagePrices;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PackageCreated::class => [
            GeneratePackagePrices::class,
        ],
        PackageUpdated::class => [
            GeneratePackagePrices::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
