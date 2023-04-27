<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomeResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\IncomingOrderResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ItemIntoWarehouseResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ListManifestResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\ListWithdrawalResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\Warehouse\EstimationResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\Warehouse\PackageResource;
use App\Http\Resources\Api\Partner\Owner\Dashboard\Warehouse\TransitResource;
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
    public function listWithdrawal(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'date' => ['nullable', 'string'],
        ]);

        $query = $repository->queries()->getWithdrawalQuery();
        if ($request->search !== "''") {
            $query->where('transaction_code', 'ilike', '%' . $request->search . '%');
        }

        if ($request->date !== "''") {
            $query->whereDate('created_at', $request->date);
        }

        $withdrawal = $query->paginate($request->input('per_page', 10));

        return $this->jsonSuccess(ListWithdrawalResource::collection($withdrawal));
    }

    public function detailIncome(Request $request, PartnerRepository $repository)
    {
        $request->validate([
            'type' => ['nullable', 'string', 'in:Dooring,Pickup,Transit,Delivery,Walkin'],
            'search' => ['nullable', 'string'],
            'date' => ['required']
        ]);

        $attributes = $request->all();
        if ($attributes['date'] === "''") {
            $attributes['date'] = Carbon::now()->format('Y-m');
        } else {
            $request->validate(['date' => 'date_format:Y-m']);
        }

        $query = $repository->queries()->getDetailIncomeDashboard($repository->getPartner()->id, $attributes['search'], $attributes['date']);
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

        $date = null;
        $status = $request->status;
        $packages = $repository->queries()->getPackagesQueryByOwner($request->type, $date)->get();
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
            'service_type' => ['nullable'],
            'category_id' => ['nullable'],
            'receipt_code' => ['nullable']
        ]);

        $query = $repository->queries()->getPackagesQuery();

        $request->whenHas('list_type', function ($value) use ($query) {
            if ($value === 'arrival') {
                $query->whereIn('status', Package::getArrivalStatus());
            } else {
                $query->where('status', Package::STATUS_IN_TRANSIT);
            }
        });

        $request->whenHas('service_type', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['service_type' => [Rule::in(Service::getAvailableType())]]);
                $query->where('service_code', $value);
            }
        });

        $request->whenHas('receipt_code', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['receipt_code' => ['exists:codes,content']]);
                $query->whereHas('code', function ($code) use ($value) {
                    $code->where('content', $value);
                });
            }
        });

        $request->whenHas('category_id', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['receipt_code' => ['exists:category_items,id']]);
                $query->whereHas('items', function ($category) use ($value) {
                    $category->where('category_item_id', $value);
                });
            }
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

        $query = $repository->queries()->getDeliveriesQueryByOwner();
        $query->with(['code', 'partner:id,code,name', 'origin_partner:id,code,name', 'partner_performance', 'transporter']);

        $request->whenHas('status', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['status' => Rule::in(Delivery::STATUS_FINISHED, Delivery::STATUS_EN_ROUTE)]);
                $query->where('status', $value);
            }
        });

        $request->whenHas('type', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['type' => Rule::in(Delivery::TYPE_PICKUP, Delivery::TYPE_TRANSIT, Delivery::TYPE_DOORING)]);
                $query->where('type', $value);
            }
        });

        $request->whenHas('search', function ($value) use ($query) {
            if ($value !== "''") {
                $query->whereHas('code', function ($q) use ($value) {
                    $q->where('content', 'ilike', '%' . $value . '%');
                });
            }
        });

        $deliveries = $query->paginate($request->input('per_page', 10));
        return $this->jsonSuccess(ListManifestResource::collection($deliveries));
    }

    public function estimateOfWarehouse(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'date' => ['nullable',],
            'status' => ['nullable', 'string'],
            'code' => ['nullable', 'string']
        ]);

        $query = $repository->queries()->getPackagesQuery();

        $packageStatus = [
            Package::STATUS_WAITING_FOR_ESTIMATING,
            Package::STATUS_ESTIMATING,
            Package::STATUS_ESTIMATED
        ];

        $query->whereIn('status', $packageStatus);

        $request->whenHas('status', function ($v) use ($query, $request) {
            if ($request->status !== "''") {
                $request->validate(['status' => 'in:done,not']);
                if ($v === 'done') {
                    $query->where('status', Package::STATUS_ESTIMATED);
                }

                if ($v === 'not') {
                    $query->whereIn('status', [Package::STATUS_WAITING_FOR_ESTIMATING, Package::STATUS_ESTIMATING]);
                }
            }
        });

        $request->whenHas('code', function ($v) use ($query) {
            if ($v !== "''") {
                $query->whereHas('code', function ($q) use ($v) {
                    $q->where('content', 'ilike', '%' . $v . '%');
                });
            }
        });

        $request->whenHas('date', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['date' => 'date']);
                $query->whereDate('created_at', $value);
            }
        });

        $result = $query->paginate($request->input('per_page', 10));

        return $this->jsonSuccess(EstimationResource::collection($result));
    }

    public function packOfWarehouse(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'date' => ['nullable'],
            'status' => ['nullable', 'string'],
            'code' => ['nullable', 'string']
        ]);

        $query = $repository->queries()->getPackagesQuery();

        $packageStatus = [
            Package::STATUS_PACKING,
            Package::STATUS_PACKED,
        ];

        $query->whereIn('status', $packageStatus);

        $request->whenHas('status', function ($v) use ($query, $request) {
            if ($request->status !== "''") {
                $request->validate(['status' => 'in:done,not']);
                if ($v === 'done') {
                    $query->where('status', Package::STATUS_PACKED);
                }

                if ($v === 'not') {
                    $query->whereIn('status', [Package::STATUS_PACKING]);
                }
            }
        });

        $request->whenHas('code', function ($v) use ($query) {
            if ($v !== "''") {
                $query->whereHas('code', function ($q) use ($v) {
                    $q->where('content', 'ilike', '%' . $v . '%');
                });
            }
        });

        $request->whenHas('date', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['date' => 'date']);
                $query->whereDate('created_at', $value);
            }
        });

        $result = $query->paginate($request->input('per_page', 10));

        return $this->jsonSuccess(PackageResource::collection($result));
    }

    public function transitOfWarehouse(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'date' => ['nullable'],
            'code' => ['nullable', 'string']
        ]);

        $query = $repository->queries()->getDeliveriesTransitByOwner();

        $request->whenHas('date', function ($value) use ($query, $request) {
            if ($value !== "''") {
                $request->validate(['date' => 'date']);
                $query->whereDate('created_at', $value);
            }
        });

        $request->whenHas('code', function ($v) use ($query) {
            if ($v !== "''") {
                $query->whereHas('code', function ($q) use ($v) {
                    $q->where('content', 'ilike', '%' . $v . '%');
                });
            }
        });

        $result = $query->paginate($request->input('per_page', 10));

        return $this->jsonSuccess(TransitResource::collection($result));
    }
}
