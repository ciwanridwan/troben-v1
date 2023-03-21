<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomeResource;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    # todo income partner
    /**
     *
     */
    public function income(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'date' => ['required']
        ]);
        $date = $request->date;
        $partnerType = $repository->getPartner()->type;
        $partnerId = $repository->getPartner()->id;

        switch ($partnerType) {
            case Partner::TYPE_POOL:
                # code...
                break;
            case Partner::TYPE_TRANSPORTER:
                # code...
                break;
            default:
                $query = $repository->queries()->getDashboardIncome($partnerId, $date);
                $result = collect(DB::select($query))->map(function ($q) {
                   $q->balance = intval($q->balance);
                   $q->current_income = intval($q->current_income);
                   $q->previous_income = intval($q->previous_income);
                   $q->increased_income = intval($q->increased_income);

                   return $q;
                })->values();
                break;
        }

        return $this->jsonSuccess(IncomeResource::make($result));
    }
}
