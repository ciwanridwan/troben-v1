<?php

namespace App\Http\Controllers;

use App\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\JsonResource;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param JsonResource $resource
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonSuccess(JsonResource $resource, ?Request $request = null): JsonResponse
    {
        return (new Response(Response::RC_SUCCESS, $resource))->json($request);
    }
}
