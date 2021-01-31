<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Jalameta\Attachments\JPSAttachment;
use App\Database\Schema\Grammars\PostgresGrammar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        JPSAttachment::$runMigrations = false;
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::connection('pgsql')->setSchemaGrammar(new PostgresGrammar());
    }
}
