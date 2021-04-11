<?php

namespace App\Http\Controllers;

use App\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param \Illuminate\Http\Resources\Json\JsonResource|null $resource
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonSuccess(?JsonResource $resource = null, ?Request $request = null): JsonResponse
    {
        return (new Response(Response::RC_SUCCESS, $resource ?? []))->json($request);
    }

    public function coming(): JsonResponse
    {
        return $this->jsonSuccess(new JsonResource([
            'message' => 'Humming is fun, we\'re still working on it 😘',
        ]));
    }
}
