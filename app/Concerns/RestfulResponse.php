<?php

namespace App\Concerns;

use App\Http\Response;
use Illuminate\Http\JsonResponse;

trait RestfulResponse
{
    /**
     * Success REST response.
     *
     * @param array  $data
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function success(array $data = [], $code = Response::RC_SUCCESS): JsonResponse
    {
        $resp = new Response($code, $data);

        return response()->json($resp->getResponseData(request()), $resp->resolveHttpCode());
    }

    /**
     * Success NO CONTENT response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function noContent(): JsonResponse
    {
        $resp = new Response(Response::RC_ACCEPTED_NO_CONTENT);

        return response()->json($resp->getResponseData(request()), $resp->resolveHttpCode());
    }
}
