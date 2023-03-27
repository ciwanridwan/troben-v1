<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Packages\Package;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function income()
    {

    }

    public function itemIntoWarehouse()
    {

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
