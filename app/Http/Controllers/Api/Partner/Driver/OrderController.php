<?php

namespace App\Http\Controllers\Api\Partner\Driver;

use App\Models\Code;
use App\Models\Deliveries\Deliverable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Models\Deliveries\Delivery;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getDeliveriesQuery();

        // skip cancelled order
        $query->where('status', '!=', Delivery::STATUS_CANCELLED)
        ->where(function($q) use ($request) {
            $q->when($request->input('delivery_status'), fn (Builder $builder, $input) => $builder->where('status', $input));
            $q->when($request->input('delivery_type'), fn (Builder $builder, $input) => $builder->where('type', $input));
        });

        $query->with(['packages.origin_district','packages.origin_sub_district','packages.destination_sub_district','packages.code','item_codes.codeable','partner_performance']);

        $query->orderByDesc('created_at');

        return $this->jsonSuccess(DeliveryResource::collection($query->paginate($request->input('per_page', 15))), null, true);
    }
    public function show(Delivery $delivery): JsonResponse
    {
        return $this->jsonSuccess(DeliveryResource::make($delivery->load(
            'code',
            'partner',
            'packages',
            'packages.origin_district',
            'packages.origin_sub_district',
            'packages.destination_sub_district',
            'packages.items',
            'driver',
            'transporter',
            'item_codes.codeable'
        )));
    }

    public function detailDeliveries(Request $request, PartnerRepository $repository): JsonResponse
    {
        $items = Code::select('id')
            ->whereIn('content', $request->codes)
            ->pluck('id')->toArray();

        $dataError = [];
        $arrDeliveries = [];
        $data = [];
        foreach ($items as $barang) {
            $deliveries = Deliverable::where('deliverable_type', 'App\Models\Code')
                ->where('status', 'prepared_by_origin_warehouse')
                ->with('delivery.assigned_to')
                ->whereHas('delivery', function ($q) use ($repository) {
                    $q->where('status', Delivery::STATUS_ACCEPTED);
                })
                ->where('deliverable_id', $barang)
                ->first();
            if ($deliveries == null || $deliveries->delivery->assigned_to->user_id != $repository->getDataUser()->id) {
                $datas = Deliverable::where('deliverable_type', 'App\Models\Code')
                    ->where('deliverable_id', $barang)
                    ->latest('updated_at')
                    ->first();
                $dataError[] = $datas->delivery_id;
            } else {
                $arrDeliveries[] = $deliveries->delivery_id;
            }
        }
        if ($arrDeliveries != []) {
            $data = $this->is_scanned($arrDeliveries, $request->codes);
        }
        if ($dataError != []) {
            $is_error = true;
            $dataError = $this->is_scanned($dataError, $request->codes, $is_error);
        }

        $things = [
            'deliveries' => $data,
            'error_deliveries' => $dataError
        ];
        return $this->jsonSuccess(new JsonResource($things));
    }

    public function is_scanned(array $arrDeliveries, array $codes, bool $is_error = false)
    {
        $deliveries = Delivery::whereIn('id', $arrDeliveries)
            ->with('code', 'packages.code', 'packages.items.codes', 'origin_partner', 'partner', 'assigned_to.user')
            ->get()
            ->toarray();

        foreach ($deliveries as $delivery) {
            foreach ($delivery['packages'] as $package) {
                foreach ($package['items'] as $item) {
                    foreach ($item['codes'] as $code) {
                        $is_scanned = false;
                        if (in_array($code['content'], $codes)) {
                            $is_scanned = true;
                        }
                        $arrItems[] = array_merge([
                            'code' => $code['content'],
                            'weight' => $item['weight'],
                            'is_scanned' => $is_scanned
                        ]);
                    }
                }
                $packages[] = array_merge([
                    'code' => $package['code']['content'],
                    'qty' => count($arrItems),
                    'items' => $arrItems
                ]);
                unset($arrItems);
            }
            if ($is_error == true) {
                $data[] = array_merge([
                    'code' => $delivery['code']['content'],
                    'status' => $delivery['status'],
                    'origin_partner' => $delivery['origin_partner']['code'],
                    'destination_partner' => $delivery['partner']['code'],
                    'assigned_to' => $delivery['assigned_to']['user']['name'],
                    'packages' => $packages
                ]);
            } else {
                $data[] = array_merge([
                    'code' => $delivery['code']['content'],
                    'packages' => $packages
                ]);
            }
            unset($packages);
        }

        return $data;
    }
}
