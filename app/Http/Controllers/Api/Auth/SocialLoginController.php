<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Models\OneTimePassword;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Auth\AccountAuthentication;

class SocialLoginController extends Controller
{
    public function googleCallback(Request $request)
    {
    }
    public function facebookCallback(Request $request)
    {
    }
}
