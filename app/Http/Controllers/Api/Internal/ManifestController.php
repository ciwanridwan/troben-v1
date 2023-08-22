<?php

namespace App\Http\Controllers\Api\Internal;

use App\Actions\Deliveries\Route;
use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HeadOffice\RequestTransporterResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\AssignPartnerToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    use HasResource, DispatchesJobs;
    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var string
     */
    protected string $model = Delivery::class;

    /**
     * @var array
     */
    protected array $rules;

    public function __construct()
    {
        $this->rules = [
            'search' => ['nullable'],
        ];

        $this->baseBuilder();
        $this->query->whereHas('packages');
    }

    public function getSearch(Request $request)
    {
        $this->query->whereHas('packages');
        $this->query->whereHas('code', function ($query) use ($request) {
            $query->search($request->search, 'content');
        });
        return $this;
    }

    public function dataRelation()
    {
        $this->query->with(['code', 'partner', 'origin_partner', 'packages', 'assigned_to.user']);
        return $this;
    }

    public function paginateWithTransformData()
    {
        $paginator = $this->query->orderBy('created_at', 'desc')->paginate(request('per_page', 15));
        $paginator->getCollection()->transform(function ($item) {
            $item->packages->each(fn ($package) => $package->items->each(fn ($package_item) => $item->weight_borne_total += $package_item->weight_borne));
            return $item;
        });
        return $paginator;
    }

    public function index(Request $request)
    {
        if ($request->partner) {
            return $this->getPartnerTransporter($request);
        }

        $this->getSearch($request);
        $this->dataRelation();

        return (new Response(Response::RC_SUCCESS, $this->paginateWithTransformData()));
    }

    public function requestTransporter(Request $request)
    {
        if ($request->partner) {
            // this is for search list available mitra
            return $this->getPartnerTransporter($request);
        }
        if ($search = $request->get('search')) {
            $this->query->whereHas('code', function ($q) use ($search) {
                $q->where('content', $search);
            });
        }
        $this->query->where('status', Delivery::STATUS_WAITING_ASSIGN_PARTNER);
        $this->getSearch($request);
        $this->dataRelation();

        return $this->jsonSuccess(RequestTransporterResource::collection($this->paginateWithTransformData()));
    }

    public function assign(Delivery $delivery, Partner $partner)
    {
        $job = new AssignPartnerToDelivery($delivery, $partner);
        $this->dispatch($job);
        return (new Response(Response::RC_SUCCESS))->json();
    }

    public function getPartnerTransporter(Request $request): JsonResponse
    {
        if ($request->delivery_hash) {
            $resetQuery = false;
            $query = Partner::query();

            $delivery = Delivery::byHash($request->delivery_hash);
            $packages = $delivery->packages;

            foreach ($packages as $package) {
                $fromPickup = false;
                if ($package->deliveries->count() <= 2) {
                    $fromPickup = true;
                }
            }

            $originPartner = $delivery->origin_partner;
            if ($originPartner->isJabodetabek() && $fromPickup) {
                // partner pickup for jabodetabek, hardcoded to this
                $query->where('code', 'MTM-CGK-00');
            } else {
                foreach ($packages as $package) {
                    if (!is_null($package->deliveryRoutes)) {
                        $partnerCode = Route::setPartnerTransporter($package->deliveryRoutes);
                        if (!is_null($partnerCode)) {
                            if (!is_array($partnerCode)) {
                                $partnerCode = [$partnerCode];
                            }
                            $query->whereIn('code', $partnerCode);
                        }
                    } else {
                        // list all partner business, transporter
                        $query->whereIn('type', [Partner::TYPE_BUSINESS, Partner::TYPE_TRANSPORTER]);
                    }
                }
            }

            // todo checker for dooring
            if ($delivery->type === Delivery::TYPE_DOORING) {
                $package = $delivery->packages()->first();
                $route = Route::getWarehousePartner($delivery->origin_partner->code, $package);
                // todo check logic condition
                if (!is_null($route) || !empty($route)) {
                    if (($route->code_mtak_1 === $route->code_dooring) && is_null($route->code_mtak_1_dest)) {
                        $query->where('code', $route->code_mtak_1);
                    }
                }
            }

            if ($request->has('search')) {
                if (!is_null($request->search) || $request->search != "") {
                    $resetQuery = true;
                }
            }

            // if searching enable, force override to partner
            if ($resetQuery) {
                $query = Partner::query();
                $query->whereIn('type', [Partner::TYPE_BUSINESS, Partner::TYPE_TRANSPORTER]);

                $request->whenHas('search', function ($value) use ($query) {
                    $query->where(function ($query) use ($value) {
                        $query->search($value);
                    });
                });
            }

            return (new Response(Response::RC_SUCCESS, $query->paginate(request('per_page', 15))))->json();
        } else {
            return (new Response(Response::RC_DATA_NOT_FOUND, ['Message' => 'Mitra Belum Tersedia']))->json();
        }
    }
}
