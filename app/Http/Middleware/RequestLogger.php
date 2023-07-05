<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $agent = $request->header('User-Agent');
        $user = 'guest';
        $auth = $request->header('Authorization');
        if ($auth && strpos( $auth, 'Bearer' ) !== false) {
            $jwt = explode('Bearer', $auth);
            if (count($jwt) >= 2) {
                $jwtItem = explode('.', trim($jwt[1]));
		if (count($jwtItem) >= 2) {
                $claim = base64_decode($jwtItem[1]);
                $claims = json_decode($claim, true);
                
                if (isset($claims['sub'])) {
                    $user = $claims['sub'];
                }
                if (isset($claims['role'])) {
                    $user = $claims['role'] . ' - ' . $user;
                }
		}
            }
        }

        // $host = $request->getHost();
        $host = config('app.url');

        $payload = [
            'host' => $host,
            'path' => $request->path(),
            'method' => $request->method(),

            'agent' => $agent,
            'user' => $user,

            'request' => json_encode($request->all()),
            'ip' => $request->ip(),
        ];

        DB::table('log_requests')->insert($payload);

        return $response;
    }
}
