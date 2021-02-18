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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config' => $this->app->basePath('config'),
            ], 'notification-channels-qontak');
        }

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
        $this->mergeConfigFrom(
            __DIR__.'/../config/qontak.php',
            'qontak'
        );
    }
}
