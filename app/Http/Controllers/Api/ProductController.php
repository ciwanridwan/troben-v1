<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Products\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Jobs\Products\CreateNewProduct;

class ProductController extends Controller
{
    /**
     * Get Product List
     * Route Path       : {API_DOMAIN}/product
     * Route Name       : api.product
     * Route Method     : GET.
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return $this->jsonSuccess(ProductResource::collection(Product::query()->orderBy('id')->get()));
    }

    public function store(Request $request)
    {
        $uploadedFile = $request->file('logo');

        $job = new CreateNewProduct($request->all(), $uploadedFile);
        $this->dispatchNow($job);

        // do when success
    }
}
