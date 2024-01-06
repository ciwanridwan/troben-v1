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
// use App\Jobs\Deliveries\Actions\CreateNewManifest;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Resources\Api\Delivery\WarehouseManifestResource;
use App\Jobs\Deliveries\Actions\V2\ProcessFromCodeToDelivery;
use App\Jobs\Deliveries\Actions\V2\CreateNewManifest;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        if ($request->has('q') && $request->get('q')) {
            $id = Code::select('codeable_id')
                ->where('content', 'like', '%' . $request->q . '%')
                ->pluck('codeable_id');
            if ($id->count() == 0) {
                return (new Response(Response::RC_DATA_NOT_FOUND))->json();
            }
            $query->whereIn('id', $id)->get();
        }

        $query->with(['partner', 'transporter', 'item_codes.codeable', 'code.scan_item_codes.codeable', 'code.scan_receipt_codes', 'packages', 'partner_performance']);

        return $this->jsonSuccess(DeliveryResource::collection($query->orderBy('created_at', 'desc')->paginate($request->input('per_page'))), null, true);
    }

    // new script to create manifest
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Supports\Repositories\PartnerRepository $repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, PartnerRepository $repository): JsonResponse
    {
        $packagesInManifest = $this->checkPackages($request->all());
        if ($packagesInManifest) {
            Log::info('Package cant insert to delivery, because still progress in delivery', [$request->code]);
            return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Package has entered in delivery']))->json();
        }
        $job = new CreateNewManifest($repository->getPartner(), $request->all());
        $this->dispatchNow($job);

        $this->insertPackagesToDelivery($request->all(), $job->delivery);

        return $this->jsonSuccess();
    }
    // end

    public function show(Delivery $delivery): JsonResponse
    {
        return $this->jsonSuccess(WarehouseManifestResource::make($delivery->load(
            'origin_partner',
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

        $dataError = [];
        $arrDeliveries = [];
        $data = [];
        foreach ($items as $barang) {
            $deliveries = Deliverable::select('delivery_id')
                ->where('deliverable_type', 'App\Models\Code')
                ->where('status', 'load_by_driver')
                ->whereHas('delivery', function ($q) use ($repository) {
                    $q->where('partner_id', $repository->getPartner()->id);
                    $q->where('status', Delivery::STATUS_FINISHED);
                    $q->orwhere('status', Delivery::STATUS_EN_ROUTE);
                })
                ->where('deliverable_id', $barang)
                ->pluck('delivery_id')->toArray();
            if ($deliveries == []) {
                $datas = Deliverable::where('deliverable_type', 'App\Models\Code')
                    ->where('deliverable_id', $barang)
                    ->latest('updated_at')
                    ->first();
                $dataError[] = $datas->delivery_id;
            } else {
                $arrDeliveries[] = $deliveries[0];
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

    /** Insert packages to new delivery */
    public function insertPackagesToDelivery($request, Delivery $delivery): void
    {
        $inputs = array_merge($request);
        if (count($inputs['code'])) {
            // code package
            $q = "select content  from codes c where
            codeable_type = 'App\Models\Packages\Package' and
            codeable_id  in (

            select package_id  from package_items pi2 where id in (

                select codeable_id from codes where codeable_type ='App\Models\Packages\Item' and content in ('%s') order by codeable_id desc
            )
            group by package_id
            )";
            $idPackages = collect(DB::select(sprintf($q, implode("','", $inputs['code']))))->pluck('content')->toArray();

            foreach ($idPackages as $idp) {
                $inputs['code'][] = $idp;
            }
        }
        $inputs['code'] = array_unique($inputs['code']);

        $inputs['status'] = Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE;
        $inputs['role'] = UserablePivot::ROLE_WAREHOUSE;
        $job = new ProcessFromCodeToDelivery(
            $delivery,
            $inputs
        );

        $this->dispatchNow($job);
    }

    /**Check packages if exists entry to manifest */
    public function checkPackages($request)
    {
        $code = $request['code'];
        $check = 0;
        $checks = [];
        $result = false;

        $packages = Code::query()->whereIn('content', $code)->where('codeable_type', Package::class)->with('codeable')->get();
        foreach ($packages as $value) {
            $package = $value->codeable;
            if ($package->status === 'manifested') {
                $check = 1;
            }

            array_push($checks, $check);
        }

        if (in_array(1, $checks)) {
            $result = true;
        }

        return $result;
    }
}
