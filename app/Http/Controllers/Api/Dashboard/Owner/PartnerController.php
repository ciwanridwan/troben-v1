<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomeResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ItemIntoWarehouseResource;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
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
            'type' => ['required', 'in:arrival,departure'],
            'date' => ['required'],
            'status' => ['nullable']
        ]);

        $currentDate = $request->date;
        $query = $repository->queries()->getPackagesQueryByOwner($request->type, $currentDate);
        $currentIdPackages = $query->get()->pluck('id')->toArray();
        $currentTotalItem = collect(DB::select($repository->queries()->getTotalItem($currentIdPackages)))->first();
        $itemPerday = collect(DB::select($repository->queries()->getHistoryItemPerday($currentIdPackages)))->values()->toArray();
        
        $previousDate = Carbon::createFromFormat('m-Y', $request->date)->startOfMonth()->subMonth()->format('m-Y');
        $queryPreviousPackages = $repository->queries()->getPackagesQueryByOwner($request->type, $previousDate);
        $previousIdPackages = $queryPreviousPackages->get()->pluck('id')->toArray();
        $previousTotalItem = collect(DB::select($repository->queries()->getTotalItem($previousIdPackages)))->first();

        $status = $request->status;
        $packages = $repository->queries()->getPackagesQueryByOwner($request->type, $currentDate)->get();
        $itemInWarehouse = $packages->map(function ($r) {
            $result = [
                'updated_at' => $r->updated_at->format('y-M-d'),
                'code' => $r->code->content,
                'total_qty' => $r->items->sum('qty'),
                'total_weight' => $r->total_weight,
                'status' => $r->status  
            ];
            return $result;
        })->filter(function ($r) use ($status) {
            if (!is_null($status) && $r['status'] === $status) {
                return true;
            } elseif (is_null($status)) {
                return true;
            } else {
                return false;
            }
        })->values()->toArray();

        $totalItems = [
            'total_current_item' => $currentTotalItem->total_item,
            'total_previous_item' => $previousTotalItem->total_item,
            'enhancement' => $currentTotalItem->total_item - $previousTotalItem->total_item,     
            'item_join_perday' => $itemPerday,
            'item_in_warehouse' => $itemInWarehouse, 
        ];


        return $this->jsonSuccess(ItemIntoWarehouseResource::make($totalItems));
    }
}
