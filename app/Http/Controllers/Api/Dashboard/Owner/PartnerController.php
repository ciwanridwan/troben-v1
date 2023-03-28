<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomeResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomingOrderResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ItemIntoWarehouseResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ListManifestResource;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Service;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
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
                    $detailReceipt = '';
                    $r->request_amount = intval($r->request_amount);
                    $r->total_accepted = intval($r->total_accepted);
                    $r->detail = $detailReceipt;
                    return $r;
                })->values();

                $result = $this->paginate($disbursmentHistory, 10);
                break;
        }

        return (new Response(Response::RC_SUCCESS, $result))->json();
        // return $this->jsonSuccess(IncomeResource::make($result));
    }

    public function detailIncome(Request $request, PartnerRepository $repository)
    {
        $request->validate([
            'type' => ['nullable', 'string', 'in:Dooring,Pickup,Transit,Delivery,Walkin'],
            'search' => ['nullable', 'string']
        ]);

        $attributes = $request->all();

        $query = $repository->queries()->getDetailIncomeDashboard($repository->getPartner()->id, $attributes['search']);
        $result = collect(DB::select($query))->groupBy('package_code')->map(function ($k, $v) {
            $k->map(function ($q) {
                $q->amount = intval($q->amount);
            });

            $serviceFee = $k->where('description', 'service')->where('type', 'deposit')->first();
            $pickupFee = $k->where('description', 'pickup')->where('type', 'deposit')->first();
            $handlingFee = $k->where('description', 'handling')->where('type', 'deposit')->first();
            $insuranceFee = $k->where('description', 'insurance')->where('type', 'deposit')->first();
            $dooringFee = $k->where('description', 'dooring')->where('type', 'deposit')->first();
            $transitFee = $k->where('description', 'transit')->where('type', 'deposit')->first();
            $deliveryFee = $k->where('description', 'delivery')->where('type', 'deposit')->first();
            $discountFee = $k->where('type', 'discount')->first();

            $totalAmount = 0;
            // $penaltyIncome = $k->where('type', 'penalty')->first();

            $subber = ['penalty', 'discount', 'withdraw'];
            $totalAmount = $k->whereNotIn('type', $subber)->sum('amount');
            $totalSubber = $k->whereIn('type', $subber)->sum('amount');

            $totalAmount = $totalAmount - $totalSubber;

            if (!is_null($serviceFee) && !is_null($pickupFee)) {
                $receivedType = 'Pickup';
            } elseif (!is_null($dooringFee)) {
                $receivedType = 'Dooring';
            } elseif (!is_null($transitFee)) {
                $receivedType = 'Transit';
            } elseif (!is_null($deliveryFee)) {
                $receivedType = 'Delivery';
            } else {
                $receivedType = 'Walkin';
            }

            return [
                'package_code' => $k[0]->package_code,
                'service_fee' => $serviceFee ? $serviceFee->amount : 0,
                'pickup_fee' => $pickupFee ? $pickupFee->amount : 0,
                'handling_fee' => $handlingFee ? $handlingFee->amount : 0,
                'insurance_fee' => $insuranceFee ? $insuranceFee->amount : 0,
                'dooring_fee' => $dooringFee ? $dooringFee->amount : 0,
                'transit_fee' => $transitFee ? $transitFee->amount : 0,
                'delivery_fee' => $deliveryFee ? $deliveryFee->amount : 0,
                'discount_fee' => $discountFee ? $discountFee->amount : 0,
                'total_amount' => $totalAmount,
                'type' => $receivedType
                // 'detail' => $k
            ];
        })->filter(function ($r) use ($attributes) {
            if ($r['type'] === $attributes['received_type']) {
                return true;
            } elseif ($attributes['received_type'] === "''") {
                return true;
            } else {
                return false;
            }
        })->values();

        return (new Response(Response::RC_SUCCESS, $this->paginate($result, $request->input('per_page'))))->json();
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

    public function listManifest(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'search' =>  ['nullable', 'string']
        ]);

        $query = $repository->queries()->getDeliveriesQuery();
        $query->with(['code', 'partner:id,code,name', 'origin_partner:id,code,name', 'partner_performance']);

        $request->whenHas('status', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['status' => Rule::in(Delivery::STATUS_FINISHED,Delivery::STATUS_EN_ROUTE)]);
                $query->where('status', $value);
            }
        });

        $request->whenHas('type', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['type' => Rule::in(Delivery::TYPE_TRANSIT,Delivery::TYPE_DOORING)]);
                $query->where('type', $value);
            }
        });

        $request->whenHas('search', function ($value) use ($query) {
            if ($value !== "''") {
                $query->whereHas('code', function ($q) use ($value) {
                    $q->where('content', 'ilike', '%'.$value.'%');
                });
            }
        });
        
        $deliveries = $query->paginate($request->input('per_page', 10));
        return $this->jsonSuccess(ListManifestResource::collection($deliveries));
        // return (new Response(Response::RC_SUCCESS, $deliveries))->json();
    }

    public function estimateOfWarehouse()
    {
        // todo, take a rest because your tired
    }

    public function packOfWarehouse()
    {
        // todo, take a rest because your tired
    }

    public function transitOfWarehouse()
    {
        // todo, take a rest because your tired
    }
}
