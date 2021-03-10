<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Handling;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HandlingController extends Controller
{
    public function index(): LengthAwarePaginator
    {
        $query = Handling::query();

        return $query->paginate();
    }
}
