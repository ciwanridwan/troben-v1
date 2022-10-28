<?php

namespace App\Http\Middleware;

use App\Actions\Auth\AccountAuthentication;
use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Offices\Office;
use App\Models\User;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Throwable
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('token');

        throw_if(! $token, new Error(Response::RC_MISSING_AUTHENTICATION_HEADER));

        try {
            $credentials = JWT::decode($token, config('services.jwt_secret'), ['HS256']);
        } catch (ExpiredException $e) {
            throw new Error(Response::RC_JWT_EXPIRED);
        } catch (\Exception $e) {
            throw new Error(Response::RC_JWT_ERROR_DECODING);
        }
        $user = Office::find($credentials->data->id);
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;
        return $next($request);
    }
}
