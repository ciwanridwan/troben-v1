<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomeResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ItemIntoWarehouseResource;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    /** @var Builder $query */
    protected Builder $query;

    # todo income partner
    /**
     * get total income, request disbursment on dashboard
     * @param Request $request
     * @param PartnerRepository $repository
     */
    public function income(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'date' => ['required'],
            'search' => ['nullable'],
            'disbursment_date' => ['nullable'],
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
                $queryMainIncome = $repository->queries()->getDashboardIncome($partnerId, $date);
                $mainIncome = collect(DB::select($queryMainIncome))->map(function ($q) {
                   $q->balance = intval($q->balance);
                   $q->current_income = intval($q->current_income);
                   $q->previous_income = intval($q->previous_income);
                   $q->increased_income = intval($q->increased_income);

                   return $q;
                })->first();

                $queryIncomePerDay = $repository->queries()->getIncomePerDay($partnerId, $date);
                $incomePerDay = collect(DB::select($queryIncomePerDay))->map(function ($r) {
                    $r->amount = intval($r->amount);

                    return $r;
                })->toArray();

                $queryDisbursHistory = $repository->queries()->getDisbursmentHistory($partnerId);
                $disbursmentHistory = collect(DB::select($queryDisbursHistory))->map(function ($r) {
                    $r->request_amonut = intval($r->request_amount);
                    $r->total_accepted = intval($r->total_accepted);
                    return $r;
                })->toArray();

                $result = [
                    'income' => $mainIncome,
                    'income_per_day' => $incomePerDay,
                    'disbursment_history' => $disbursmentHistory
                ];
                break;
        }

        return $this->jsonSuccess(IncomeResource::make($result));
    }

    public function itemIntoWarehouse(Request $request, PartnerRepository $repository)
    {
        $request->validate([
            'type' => ['required', 'in:arrival,departure']
        ]);

        $this->query = $repository->queries()->getPackagesQueryByOwner($request->type);

        return $this->jsonSuccess(ItemIntoWarehouseResource::collection($this->query->paginate($request->input('per_page'))));
    }
}
