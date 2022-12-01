<?php

namespace App\Supports;

use App\Models\Customers\Customer;
use Firebase\JWT\JWT;
use App\Models\User;

class JwtAuth
{
    public static function generateJwt($user)
    {
        $iss = 'TBCore';
        if (config('app.env') != 'production') {
            $iss .= '-'.config('app.env');
        }

        if ($user instanceof User) {
            $role = 'user';
        } else if ($user instanceof Customer) {
            $role = 'customer';
        } else {
            $role = 'office';
        }

        return JWT::encode([
            'iat' => time(),
            'exp' => time() + (60 * config('jwt.ttl')),
            'role' => $role,
            'sub' => $user->getKey(),
            'iss' => $iss,
        ], config('services.jwt_secret'));
    }
}
