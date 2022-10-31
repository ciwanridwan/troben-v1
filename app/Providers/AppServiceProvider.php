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
use App\Http\Resources\Account\JWTUserResource;
use App\Supports\JwtAuth;
use Firebase\JWT\JWT;
use Illuminate\Routing\UrlGenerator;

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

        if (in_array(env('APP_ENV', 'local'), ['production', 'staging'])) {
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        DB::connection('pgsql')->setSchemaGrammar(new PostgresGrammar());

        $this->loadViewsFrom(resource_path('/antd-pro/views'), 'antd');

        if (in_array(env('APP_ENV', 'local'), ['production', 'staging'])) {
            $url->formatScheme('https');
        }

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
                    $iss = 'TBCore';
                    if (config('app.env') != 'production') {
                        $iss .= '-'.config('app.env');
                    }

                    $view->with('laravelJs', [
                        'is_authenticated' => auth()->check(),
                        'jwt_token' => auth()->user()
                            ? JwtAuth::generateJwt(auth()->user())
                            : null,
                        'user' => auth()->user(),
                        'routes' => $collection,
                        'current_route' => empty(request()->route()) ? '' : request()->route()->getName(),
                        'request' => request()->all(),
                        'fcm' => [
                            'cloud_messaging' => config('trawl-firebase'),
                            'service_worker_url' => mix('/js/trawl-sw.js')->toHtml(),
                        ],
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

            if (auth()->user() instanceof User and Str::startsWith($name, 'api')) {
                return true;
            }

            // add verify for admin later
            if (auth()->user() instanceof User and Str::startsWith($name, 'admin')) {
                return true;
            }

            // add verify for partner later
            if (auth()->user() instanceof User and Str::startsWith($name, 'partner')) {
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
