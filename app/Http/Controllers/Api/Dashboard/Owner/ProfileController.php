<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Owner\UpdateProfileRequest;
use App\Http\Resources\Api\Partner\Owner\InfoProfileResource;
use App\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function info(Request $request)
    {
        $owner = $request->user();

        return $this->jsonSuccess(InfoProfileResource::make($owner));
    }
    /**
     * Update profile owner of partner
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        return (new Response(Response::RC_UPDATED))->json();
    }
}
