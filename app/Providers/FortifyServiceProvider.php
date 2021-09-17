<?php

namespace App\Providers;

use App\Actions\Auth\AccountAuthentication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Partners\Pivot\UserablePivot;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Contracts\Foundation\Application;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Fortify::ignoreRoutes();

        $this->app->bind(LoginResponseContract::class, function (Application $app) {
            /** @var Request $request */
            $request = $app->make(Request::class);
            /** @var PartnerRepository $repository */
            $repository = $app->make(PartnerRepository::class);

            return response()->json([
                'redirect' => $request->user()->is_admin
                    ? route('admin.home')
                    : UserablePivot::getHomeRouteRole($repository->getScopedRole()),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::loginView(fn () => view('antd::auth.login'));

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::authenticateUsing(function (Request $request) {
            /** @var User $user */
            $user = User::query()
                ->where('email', $request->username)
                ->orWhere('username', $request->username)
                ->first();

            if ($user &&
                Hash::check($request->password, $user->password)) {
                if (empty($user->fcm_token)) {
                    AccountAuthentication::validationFcmToken($user);
                }

                return $user;
            }
        });
    }
}
