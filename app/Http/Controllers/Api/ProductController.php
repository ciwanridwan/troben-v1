<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Products\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get Product List
     * Route Path       : {API_DOMAIN}/product
     * Route Name       : api.product
     * Route Method     : GET.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function list(): JsonResponse
    {
        return $this->jsonSuccess(ProductResource::collection(Product::all()));
    }
}
