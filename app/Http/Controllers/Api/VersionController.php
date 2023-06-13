<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VersionController extends Controller
{
    /***
     * @return Version
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->has('app')) {
            $version = Version::where('app', 'LIKE', $request->app)->latest()->first();
        } else {
            $version  = Version::all();
        }

        return (new Response(Response::RC_SUCCESS, $version))->json();
    }
}
