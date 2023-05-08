<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\StoreRatingReviewRequest;
use App\Http\Resources\Api\Order\RatingAndReviewResource;
use App\Http\Response;
use App\Models\Packages\Package;
use App\Models\Packages\RatingAndReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingAndReviewController extends Controller
{
    /**
     * To create rating and review
     */
    public function store(StoreRatingReviewRequest $request): JsonResponse
    {
        $request->validated();
        $package = Package::byHash($request->package_hash);
        if (is_null($package)) {
            return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Hash not valid, please check hash again']))->json();
        }

        $result = RatingAndReview::create([
            'package_id' => $package->id,
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return $this->jsonSuccess(RatingAndReviewResource::make($result));
    }
}
