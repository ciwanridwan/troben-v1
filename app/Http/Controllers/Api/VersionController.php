<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Version;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    /***
     * @return Version
     */
    public function index(): JsonResponse
    {
        $version  = Version::all();


        return (new Response(Response::RC_SUCCESS, $version))->json();;
    }
}
