<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomeResource;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    # todo income partner
    /**
     * 
     */
    public function income(Request $request, PartnerRepository $repository): JsonResponse
    {
        $partnerType = $repository->getPartner()->type;

        switch ($partnerType) {
            case Partner::TYPE_POOL:
                # code...
                break;
            case Partner::TYPE_TRANSPORTER:
                # code...
                break;
            default:
                $income = 0;
                break;
        }

        return $this->jsonSuccess(IncomeResource::collection($partnerType));
    }
}
