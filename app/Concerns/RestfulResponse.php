<?php

namespace App\Concerns;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

trait RestfulResponse
{
    /**
     * Success REST response.
     *
     * @param array  $data
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function success(array $data = [], $message = 'SUCCESS'): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'error' => null,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    /**
     * Success NO CONTENT response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function noContent(): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'error' => 'null',
            'message' => 'NO_CONTENT',
            'data' => null,
        ], Response::HTTP_NO_CONTENT);
    }
}
