<?php

namespace App\Http\Controllers;

use App\Http\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param Arrayable $resource
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonSuccess(Arrayable $resource, ?Request $request = null): JsonResponse
    {
        return (new Response(Response::RC_SUCCESS, $resource))->json($request);
    }
}
