<?php

namespace NotificationChannels\NusaSms;

use Illuminate\Support\ServiceProvider;

class NusaSmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config' => $this->app->basePath('config'),
            ], 'notification-channels-nusasms');
        }

        $this->app->when(NusaSmsChannel::class)
            ->needs(NusaSmsClient::class)
            ->give(fn () => new NusaSmsClient(config('nusa-sms')));
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/nusa-sms.php.php',
            'nusa-sms'
        );
    }
}
