<?php

namespace App\Http\Controllers\Api;

use App\Models\Handling;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class HandlingController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Handling::query();

        return $this->jsonSuccess(JsonResource::collection($query->paginate()));
    }
}
