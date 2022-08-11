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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param JsonResource|null $resource
     * @param Request|null $request
     * @param bool|null $hasServerTime
     * @return JsonResponse
     */
    public function jsonSuccess(?JsonResource $resource = null, ?Request $request = null, ?bool $hasServerTime = null): JsonResponse
    {
        return (new Response(Response::RC_SUCCESS, $resource ?? [], $hasServerTime ?? false))->json($request);
    }

    public function jsonResponse($data): JsonResponse
    {
        $response = new Request([
            'code' => 200,
            'error' => null,
            'message' => 'Success',
            'data' => $data
        ]);
        return (new Response(Response::RC_SUCCESS, $data))->json($response);
    }

    public function coming(): JsonResponse
    {
        return $this->jsonSuccess(new JsonResource([
            'message' => 'Humming is fun, we\'re still working on it 😘',
        ]));
    }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $slicedItems = $items->forPage($page, $perPage)->values();
        return new LengthAwarePaginator($slicedItems, $items->count(), $perPage, $page, $options);
    }
}
