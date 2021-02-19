<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
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

        $this->loadViewsFrom(resource_path('/antd-pro/views'), 'antd');

        if ($this->app->runningInConsole() === false) {
            // register view composer.
            view()->composer('*', function (View $view) {
                $collection = new Collection();

                $this->getRoutes()->each(function ($item, $key) use ($collection) {
                    $collection->offsetSet($key, [
                        'uri' => $item->uri,
                        'methods' => $item->methods,
                    ]);
                });

                if (! array_key_exists('laravelJs', $view->getData())) {
                    $view->with('laravelJs', [
                        'is_authenticated' => auth()->check(),
                        'user' => auth()->user(),
                        'routes' => $collection,
                        'current_route' => empty(request()->route()) ? '' : request()->route()->getName(),
                        'request' => request()->all(),
                    ]);
                }
            });
        }
    }

    /**
     * Get route collection.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRoutes(): Collection
    {
        return collect($this->getRouteCollection())->filter(function ($route, $name) {
            if (Str::startsWith($name, 'auth')) {
                return true;
            }

            // add verify for admin later
            if (Str::startsWith($name, 'admin')) {
                return true;
            }

            if (auth()->user() instanceof User and Str::startsWith($name, 'api')) {
                return true;
            }

            return false;
        });
    }

    /**
     * Get route collection instance.
     *
     * @return array
     */
    protected function getRouteCollection(): array
    {
        return $this->app['router']->getRoutes()->getRoutesByName();
    }
}
