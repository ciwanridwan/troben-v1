<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use App\Http\Response;
use App\Models\Code;
use App\Models\Deliveries\Deliverable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use App\Jobs\Deliveries\Actions\CreateNewManifest;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Resources\Api\Delivery\WarehouseManifestResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use function PHPUnit\Framework\isEmpty;

class ManifestController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $partner = $repository->getPartner();
        $query = $repository->queries()->getDeliveriesQuery();
        $request->whenHas('arrival', function (bool $value) use ($query, $partner) {
            if ($value) {
                $query->where('partner_id', $partner->id);
            }
        });
        $request->whenHas('departure', function (bool $value) use ($query, $partner) {
            if ($value) {
                $query->where('origin_partner_id', $partner->id);
            }
        });
        $request->whenHas('delivery_type', function (array $value) use ($query) {
            $value = Arr::wrap($value);
            $query->whereIn('type', $value);
            if (in_array(Delivery::TYPE_DOORING, $value)) {
                $query->with('packages');
            }
        });
        $request->whenHas('status', function (array $value) use ($query) {
            $value = Arr::wrap($value);
            $query->whereIn('status', $value);
            if (in_array(Delivery::TYPE_DOORING, $value)) {
                $query->with('packages');
            }
        });

        if ($request->has('q')){
            $id = Code::select('codeable_id')
                ->where('content', 'like', '%'.$request->q.'%')
                ->pluck('codeable_id');
            if ($id->count() == 0){
                return (new Response(Response::RC_DATA_NOT_FOUND))->json();
            }
            $query->whereIn('id', $id)->get();
        }

        $query->with(['partner', 'transporter',  'item_codes.codeable', 'code.scan_item_codes.codeable', 'code.scan_receipt_codes', 'packages']);

        return $this->jsonSuccess(DeliveryResource::collection($query->orderBy('created_at', 'desc')->paginate($request->input('per_page'))));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Supports\Repositories\PartnerRepository $repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, PartnerRepository $repository): JsonResponse
    {
        $job = new CreateNewManifest($repository->getPartner(), $request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    public function show(Delivery $delivery): JsonResponse
    {
        return $this->jsonSuccess(WarehouseManifestResource::make($delivery->load(
            'item_codes',
            'code',
            'partner',
            'packages',
            'packages.code',
            'driver',
            'transporter',
        )));
    }

    public function detailDeliveries(Request $request, PartnerRepository $repository): JsonResponse
    {
        $items = Code::select('id')
            ->whereIn('content', $request->codes)
            ->pluck('id')->toArray();
        foreach($items as $barang){
            $deliveries = Deliverable::select('delivery_id')
                ->where('deliverable_type', 'App\Models\Code')
                ->where('status', 'load_by_driver')
                ->whereHas('delivery', function($q) use ($repository) {
                    $q->where('partner_id', $repository->getPartner()->id);
                    $q->where('status', Delivery::STATUS_FINISHED);
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
        $dataError = [];
        $data = $this->is_scanned($arrDeliveries, $request->codes);
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
