<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Auth\OtpVerification;

class OtpController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $inputs = $request->validate([
            'otp_token' => ['required'],
            'otp' => ['required', 'exists:one_time_passwords,id'],
            'device_name' => ['required'],
        ]);

        return (new OtpVerification($inputs))->verify();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function resendToken(Request $request): JsonResponse
    {
        $inputs = $request->validate([
            'otp' => ['required', 'exists:one_time_passwords,id'],
            'retry' => ['required', 'boolean'],
        ]);

        return (new OtpVerification($inputs))->resend();
    }
}
