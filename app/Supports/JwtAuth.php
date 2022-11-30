<?php

namespace App\Supports;

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
        } else {
            $role = 'customer';
        }

        return JWT::encode([
            'iat' => time(),
            'exp' => time() + ((60 * 60) * 24), // 24 hour
            'role' => $role,
            'sub' => $user->getKey(),
            'iss' => $iss,
        ], config('services.jwt_secret'));
    }
}
