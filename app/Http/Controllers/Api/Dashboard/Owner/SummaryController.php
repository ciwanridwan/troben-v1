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
        $totalOrder = $repository->queries()->getPackagesQuery()->count();

        $orderArrival = $repository->queries()->getPackagesQuery()->whereIn('status', Package::getArrivalStatus())->count();
        $orderDeparture = $repository->queries()->getPackagesQuery()->where('status', Package::STATUS_IN_TRANSIT)->count();
      
        $previousDate = Carbon::createFromFormat('m-Y', Carbon::now()->format('m-Y'))->startOfMonth()->subMonth()->format('m-Y');
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
