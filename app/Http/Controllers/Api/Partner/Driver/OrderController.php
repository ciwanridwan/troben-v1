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

        $query->when($request->input('delivery_status'), fn (Builder $builder, $input) => $builder->where('status', $input));
        $query->when($request->input('delivery_type'), fn (Builder $builder, $input) => $builder->where('type', $input));

        $query->with(['packages.origin_district', 'packages.origin_sub_district', 'packages.destination_sub_district', 'packages.code', 'item_codes.codeable']);

        $query->orderByDesc('created_at');

        return $this->jsonSuccess(DeliveryResource::collection($query->paginate($request->input('per_page', 15))));
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
        foreach($items as $barang){
            $deliveries = Deliverable::select('delivery_id')
                ->where('deliverable_type', 'App\Models\Code')
                ->where('status', 'prepared_by_origin_warehouse')
                ->whereHas('delivery', function($q) use ($repository) {
                    $q->where('status', Delivery::STATUS_WAITING_TRANSPORTER);
//                    $q->where('userable_id', $repository->getDataUser()->id);
                })
                ->where('deliverable_id', $barang)
                ->pluck('delivery_id')->toArray();
            if($deliveries == []){
                $datas = Deliverable::where('deliverable_type', 'App\Models\Code')
                    ->where('deliverable_id', $barang)
                    ->latest('updated_at')
                    ->first();
                $dataError[] = $datas->delivery_id;
            }else{
                $arrDeliveries[] = $deliveries[0];
            }
        }
        if ($arrDeliveries != []){
            $data = $this->is_scanned($arrDeliveries, $request->codes);
        }
        if ($dataError != []){
            $dataError = $this->is_scanned($dataError, $request->codes);
        }

        $things = [
            'deliveries' => $data,
            'error_deliveries' => $dataError
        ];
        return $this->jsonSuccess(new JsonResource($things));
    }

    public function is_scanned(array $arrDeliveries, array $codes)
    {
        $deliveries = Delivery::whereIn('id', $arrDeliveries)
            ->with('code','packages.code', 'packages.items.codes')
            ->get()
            ->toarray();

        foreach($deliveries as $delivery){
            foreach($delivery['packages'] as $package){
                foreach ($package['items'] as $item) {
                    foreach($item['codes'] as $code){
                        $is_scanned = false;
                        if (in_array($code['content'], $codes)){
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
            $data[] = array_merge([
                'code' => $delivery['code']['content'],
                'packages' => $packages
            ]);
            unset($packages);
        }

        return $data;
    }
}
