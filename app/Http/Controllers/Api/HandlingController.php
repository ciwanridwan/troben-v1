<?php

namespace App\Http\Controllers\Api;

use App\Casts\Package\Items\Handling;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class HandlingController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->jsonSuccess(JsonResource::collection(Handling::getTypes()));
    }
}
