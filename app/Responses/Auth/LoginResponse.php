<?php


namespace App\Responses\Auth;


class LoginResponse extends \Laravel\Fortify\Http\Responses\LoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'redirect' => url(config('fortify.home'))
            ]);
        }

        return parent::toResponse($request);
    }
}
