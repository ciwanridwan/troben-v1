<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\OtpVerify;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function verifyToken(Request $request)
    {
        $inputs = $request->validate([
            'otp_token' => ['required'],
            'otp' => ['required', 'exists:one_time_passwords,id'],
            'device_name' => ['required']
        ]);
        return (new OtpVerify($inputs))->verify();
    }
}
