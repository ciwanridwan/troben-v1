<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\CheckReceiptResource;
use App\Models\Code;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    public function receipt(Request $request, Code $code): JsonResponse
    {
        if (! $code->exists) {
            $request->validate([
                'code' => ['required']
            ]);

            /** @var Code $code */
            $code = Code::query()->with(['codeable', 'codeable.attachments', 'codeable.origin_regency', 'codeable.destination_regency', 'codeable.destination_district', 'codeable.destination_sub_district', 'codeable.origin_regency.province', 'codeable.destination_regency.province','logs'])->where('content', 'ilike', '%'.$request->code.'%')->where('codeable_type', Package::class);
        }

        return $this->jsonSuccess(CheckReceiptResource::collection($code->paginate(request()->input('per_page'))));
    }
}
