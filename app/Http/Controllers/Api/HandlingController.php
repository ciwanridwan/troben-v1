<?php

namespace App\Http\Controllers\Api;

use App\Models\Handling;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class HandlingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = Handling::query();

        return JsonResource::collection($query->paginate());
    }
}
