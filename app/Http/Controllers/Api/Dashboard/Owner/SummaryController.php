<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Packages\Package;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    public function income(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m'],
        ]);

        $currentDate = $request->date;
        if ($currentDate === "''") {
            $currentDate = Carbon::now()->format('Y-m');
        }

        $previousDate = Carbon::createFromFormat('Y-m', $currentDate)->subMonth()->format('Y-m');
        $partnerId = $repository->getPartner()->id;

        $queryMainIncome = $repository->queries()->getDashboardIncome($partnerId, $currentDate, $previousDate);

        $mainIncome = collect(DB::select($queryMainIncome))->map(function ($q) {
            $q->balance = intval($q->balance);
            $q->current_income = intval($q->current_income);
            $q->previous_income = intval($q->previous_income);
            $q->increased_income = intval($q->increased_income);

            return $q;
        })->first();

        $queryIncomePerDay = $repository->queries()->getIncomePerDay($partnerId, $currentDate);
        $incomePerDay = collect(DB::select($queryIncomePerDay))->map(function ($r) {
            $r->amount = intval($r->amount);

            return $r;
        })->toArray();

        $result = [
            'income' => $mainIncome,
            'income_per_day' => $incomePerDay,
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function itemIntoWarehouse(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:arrival,departure'],
            'date' => ['nullable', 'string']
        ]);

        $currentDate = $request->date;
        $query = $repository->queries()->getPackagesQueryByOwner($request->type, $currentDate);
        $currentIdPackages = $query->get()->pluck('id')->toArray();

        if (!empty($currentIdPackages)) {
            $currentItem = collect(DB::select($repository->queries()->getTotalItem($currentIdPackages)))->first();
            $itemPerday = collect(DB::select($repository->queries()->getHistoryItemPerday($currentIdPackages)))->values()->toArray();
            $currentTotalItem = $currentItem->total_item;
        } else {
           $currentTotalItem = 0;
           $itemPerday = [];
        }

        $previousDate = Carbon::createFromFormat('m-Y', $request->date)->startOfMonth()->subMonth()->format('m-Y');
        $queryPreviousPackages = $repository->queries()->getPackagesQueryByOwner($request->type, $previousDate);
        $previousIdPackages = $queryPreviousPackages->get()->pluck('id')->toArray();
        if (!empty($previousIdPackages)) {
            $previousItem = collect(DB::select($repository->queries()->getTotalItem($previousIdPackages)))->first();
            $previousTotalItem = $previousItem->total_item;
        } else {
            $previousTotalItem = 0;
        }

        $totalItems = [
            'total_current_item' => $currentTotalItem,
            'total_previous_item' => $previousTotalItem,
            'enhancement' => $currentTotalItem - $previousTotalItem,
            'item_join_perday' => $itemPerday
        ];

        return (new Response(Response::RC_SUCCESS, $totalItems))->json();
    }

    public function incomingOrders(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'date' => ['required']
        ]);
        if ($request->date === "''") {
            $date = Carbon::now()->format('m-Y');
        } else {
            $date = $request->date;
        }

        $currentDate = $date;
        $currentMonth = substr($currentDate, 0, 2);
        $currentYear = substr($currentDate, 3);

        $totalOrder = $repository->queries()->getPackagesQuery()->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();
        $orderArrival = $repository->queries()->getPackagesQuery()->whereIn('status', Package::getArrivalStatus())->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();
        $orderDeparture = $repository->queries()->getPackagesQuery()->where('status', Package::STATUS_IN_TRANSIT)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();

        $previousDate = Carbon::createFromFormat('m-Y', $date)->startOfMonth()->subMonth()->format('m-Y');
        $previousMonth = substr($previousDate, 0, 2);
        $previousYear = substr($previousDate, 3);
        $orderPreviousArrival = $repository->queries()->getPackagesQuery()->whereIn('status', Package::getArrivalStatus())->whereMonth('created_at', $previousMonth)->whereYear('created_at', $previousYear)->count();
        $orderPreviousDeparture = $repository->queries()->getPackagesQuery()->where('status', Package::STATUS_IN_TRANSIT)->whereMonth('created_at', $previousMonth)->whereYear('created_at', $previousYear)->count();

        $result = [
            'total_order' => $totalOrder,
            'order_come' => $orderArrival,
            'order_come_increased' => $orderArrival - $orderPreviousArrival,
            'order_out'  => $orderDeparture,
            'order_out_increased'  => $orderDeparture - $orderPreviousDeparture
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }
}
