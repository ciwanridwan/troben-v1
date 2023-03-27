<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomeResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomingOrderResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ItemIntoWarehouseResource;
use App\Http\Response;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Service;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'search' => ['nullable'],
            'date' => ['nullable'],
        ]);

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
                $queryDisbursHistory = $repository->queries()->getDisbursmentHistory($partnerId);
                $disbursmentHistory = collect(DB::select($queryDisbursHistory))->map(function ($r) {
                    $r->request_amonut = intval($r->request_amount);
                    $r->total_accepted = intval($r->total_accepted);
                    return $r;
                })->values();

                $result = $this->paginate($disbursmentHistory, 10);
                break;
        }

        return (new Response(Response::RC_SUCCESS, $result))->json();
        // return $this->jsonSuccess(IncomeResource::make($result));
    }

    /**
     * Item join to warehouse
     */
    public function itemIntoWarehouse(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:arrival,departure'],
            'status' => ['nullable']
        ]);

        // $currentDate = $request->date;
        $status = $request->status;
        $packages = $repository->queries()->getPackagesQueryByOwner($request->type)->get();
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

        $items = $this->paginate($itemInWarehouse, 10);
        return (new Response(Response::RC_SUCCESS, $items))->json();
        // return $this->jsonSuccess(ItemIntoWarehouseResource::make($totalItems));
    }

    /**
     * 
     */
    public function incomingOrders(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'list_type' => ['required', 'in:arrival,departure'],
            'service_type' => ['nullable', Rule::in(Service::getAvailableType())],
            'category_id' => ['nullable', 'exists:category_items,id'],
            'receipt_code' => ['nullable', 'exists:codes,content']
        ]);

        $query = $repository->queries()->getPackagesQuery();

        $request->whenHas('list_type', function ($value) use ($query) {
            if ($value === 'arrival') {
                $query->whereIn('status', Package::getArrivalStatus());
            } else {
                $query->where('status', Package::STATUS_IN_TRANSIT);
            }
        });

        $request->whenHas('service_type', function ($value) use ($query) {
            $query->where('service_code', $value);
        });

        $request->whenHas('receipt_code', function ($value) use ($query) {
            $query->whereHas('code', function ($code) use ($value) {
                $code->where('content', $value);
            });
        });

        $request->whenHas('category_id', function ($value) use ($query) {
            $query->whereHas('items', function ($category) use ($value) {
                $category->where('category_item_id', $value);
            });
        });

        $packages = $query->paginate($request->input('per_page', 10));

        return $this->jsonSuccess(IncomingOrderResource::collection($packages));
    }
}
