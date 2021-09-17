<?php

namespace App\Http\Controllers\Api\WMS;

use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Code;
use App\Models\Deliveries\Deliverable;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use App\Jobs\Deliveries\Actions\CreateNewManifest;
use App\Http\Resources\Api\Delivery\WarehouseManifestResource;

class ManifestController extends Controller
{
    public Collection $codes;
    public Code $code;

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
        if ($request->to == null){
            $request->to = Carbon::now();
        }
        $query->when(request()->has('regency_id'), fn ($q) => $q->where('destination_regency_id', $request->regency_id));
        $query->when(request()->has('from'), fn ($q) => $q->whereBetween('created_at', [$request->from, $request->to]));
        $query->when(request()->has('type'), fn ($q) => $q->where('type', $request->type));
        $query->when(request()->has('code'), fn ($q) => $q->whereHas('code', function ($query) use ($request) {
            $query->whereRaw("LOWER(content) like '%".strtolower($request->code)."%'");
        }));

        $query->with('partner', 'packages.items');

        return $this->jsonSuccess(DeliveryResource::collection($query->paginate($request->input('per_page'))));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Supports\Repositories\PartnerRepository $repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, PartnerRepository $repository): JsonResponse
    {
        $manifested_code = [
            'existed' => []
        ];
        $this->codes = Code::query()->whereIn('content', is_array($request['code']) ? $request['code'] : [$request['code']])->with('codeable')->get();

        foreach ($this->codes as $code) {
            /** @var Package $package */
            $package = $code->codeable instanceof Package ? $code->codeable : $code->codeable->package;
            if ($package->status == Package::STATUS_MANIFESTED) {
                array_push($manifested_code['existed'], $code->content);
            }
        }

        if (empty($manifested_code['existed'])){
            $order = new CreateNewManifest($repository->getPartner(), $request->all());
            $this->dispatchNow($order);

            $job = new ProcessFromCodeToDelivery($order->delivery, array_merge($request->only(['code']), [
                'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
                'role' => UserablePivot::ROLE_WAREHOUSE
            ]));
            $this->dispatchNow($job);
        }
        else{
            return (new Response(Response::RC_BAD_REQUEST, $manifested_code))->json();
        }
        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
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
}
