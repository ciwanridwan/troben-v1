<?php

namespace App\Auditor;

use App\Auditor\Console\PruneCommand;
use Illuminate\Support\ServiceProvider;

class AuditorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('trawlbens.auditor', function ($app) {
            return new Factory();
        });

        $this->app->alias('trawlbens.auditor', Factory::class);
        $this->app->alias('trawlbens.auditor', Auditor::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['trawlbens.auditor', 'command.auditor'];
    }
}
