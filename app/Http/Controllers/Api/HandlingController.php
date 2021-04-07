<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class HandlingController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->jsonSuccess(JsonResource::collection([/* TODO : fill this with available const in Handling */]));
    }
}
