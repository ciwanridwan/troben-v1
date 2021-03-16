<?php

namespace App\Http\Controllers\Api;

use App\Models\Handling;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HandlingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = Handling::query();

        return JsonResource::collection($query->paginate());
    }
}
