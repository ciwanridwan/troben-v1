<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): AnonymousResourceCollection
    {
        $query = $repository->queries()->getDeliveriesQuery();

//        return ;
    }
}
