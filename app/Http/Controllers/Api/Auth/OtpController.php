<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Actions\Auth\{OtpVerify, OtpResend};
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

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

        return (new OtpVerify($inputs))->verify();
    }
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function resendToken(Request $request): JsonResponse
    {
        $inputs = $request->validate([
            'otp' => ['required', 'exists:one_time_passwords,id'],
            'retry' => ['required', 'boolean']
        ]);

        return (new OtpResend($inputs))->resend();
    }
}
