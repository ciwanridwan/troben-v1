<?php

namespace App\Http\Controllers;

use App\Http\Resources\Api\Package\PackageResource;
use App\Http\Response;
use App\Jobs\Promo\UploadFilePromo;
use App\Jobs\Promo\UploadPhotoPromo;
use App\Models\Promo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(): JsonResponse
    {
        $version  = Promo::all();

        return (new Response(Response::RC_SUCCESS, $version))->json();
    }


    public function store(Request $request, Promo $promo): JsonResponse
    {
        $request->validate([
            'receipt' => 'required',
        ]);

        $job = new UploadFilePromo($promo, $request->file('receipt'));

        $this->dispatchNow($job);

        return $this->jsonSuccess(PackageResource::make($job->promo->load('attachments')));
    }
}
