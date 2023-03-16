<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Update profile owner of partner
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
    }
}
