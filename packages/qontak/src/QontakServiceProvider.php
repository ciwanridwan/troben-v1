<?php

namespace NotificationChannels\Qontak;

use Illuminate\Support\ServiceProvider;
use NotificationChannels\Qontak\WhatsApp\BusinessChannel;

class QontakServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(BusinessChannel::class)
            ->needs(QontakClient::class)
            ->give(function () {
                $config = config('qontak');

                return new QontakClient($config);
            });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
