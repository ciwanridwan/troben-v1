<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Actions\Deliveries\Route;
use App\Http\Response;
use App\Models\Code;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Builder;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Admin\Master\PartnerResource;
use App\Http\Resources\Api\Assignable\DriverTransporterResource;
use App\Http\Resources\Api\Assignable\PackageResource;
use Illuminate\Validation\Rule;

class AssignableController extends Controller
{
    public function partner(Request $request, PartnerRepository $repository): JsonResponse
    {
        $setPartner = Route::checkPackages($request->all());
        switch (true) {
            case $setPartner === 1:
                $packages = Route::getPackages($request->all());
                $partnerByRoutes = [];
                foreach ($packages as $package) {
                    if (!is_null($package->deliveryRoutes)) {
                        $partnerByRoute = Route::setPartners($package->deliveryRoutes);
                        array_push($partnerByRoutes, $partnerByRoute);
                    } else {
                        $partnerCode = null;
                    }
                }
                $partnerCode = $partnerByRoutes;
                break;
            case $setPartner === 2:
                $partnerCode = Route::generate($repository->getPartner(), $request->all());
                break;
            case $setPartner === 3:
                $partnerCode = 'all';
                break;
            default:
                $partnerCode = null;
                break;
        }

        $query = Partner::query()->where('id', '!=', $repository->getPartner()->id);

        if ($partnerCode === 'all' || is_null($partnerCode) || count($partnerCode) == 0) {
            $query->whereIn('type', [Partner::TYPE_BUSINESS, Partner::TYPE_POOL]);
        } else {
            $query->whereIn('code', $partnerCode);
        }

        $query->when(
            $request->input('code'),
            fn (Builder $builder, $code) => $builder->Where('code', 'LIKE', '%' . $code . '%')
        );

        return $this->jsonSuccess(PartnerResource::collection($query->paginate($request->input('per_page'))));
    }

    public function driver(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getTransporterDriverQuery();

        $query->when(
            $request->input('transporter_type'),
            fn (Builder $userableQuery, $type) => $userableQuery->whereHasMorph(
                'userable',
                Transporter::class,
                fn (Builder $transporterQuery) => $transporterQuery->where('type', $type)
            )
        );
        return $this->jsonSuccess(DriverTransporterResource::collection($query->paginate($request->input('per_page'))));
        // return $this->jsonSuccess(TransporterDriverResource::collection($query->paginate($request->input('per_page'))));
    }

    public function package(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', 'in:dooring,transit'],
        ]);

        $query = $repository->queries()->getPackagesQuery();
        $query->whereIn('status', [Package::STATUS_PACKED, Package::STATUS_IN_TRANSIT]);
        $query->with('estimator', 'packager', 'items', 'partner_performance');

        if ($request->has('q')) {
            $id = Code::select('codeable_id')
                ->where('content', 'like', '%' . $request->q . '%')
                ->pluck('codeable_id');
            if ($id->count() == 0) {
                return (new Response(Response::RC_DATA_NOT_FOUND))->json();
            }
            $query->whereIn('id', $id);
        }

        $request->whenHas('status', fn ($value) => $query->where('status', $value));
        $query->whereDoesntHave(
            'deliveries',
            fn (Builder $builder) => $builder
                ->where('origin_partner_id', '!=', $repository->getPartner()->id)
                ->whereNotIn('type', [
                    Delivery::TYPE_TRANSIT,
                    Delivery::TYPE_DOORING,
                    Delivery::TYPE_RETURN,
                ])
        );

        $data = $query->paginate($request->input('per_page'));
        $packages = $data->getCollection();
        $partnerId = $repository->getPartner()->id;

        $type = $request->get('type');

        if ($type == 'dooring') {
            $packages = $this->getPackagesDooring($packages, $partnerId);
        }
        if ($type == 'transit') {
            $packages = $this->getPackagesTransit($packages, $partnerId);
        }

        $result = $data->setCollection($packages);

        return $this->jsonSuccess(PackageResource::collection($result), null, true);
    }

    public function checkPackages(Request $request)
    {
        $request->validate([
            'package_code' => ['required', 'array', Rule::exists('codes', 'content')->whereIn('codeable_type', [
                Package::class
            ])]
        ]);

        $packages = Code::query()->whereIn('content', $request->package_code)->with('codeable')->get()->map(function ($q) {
            return $q->codeable;
        });

        $variant = 0;
        $allVariant = [];
        // $partner = $repository->getPartner();
        $firstPackage = $packages->first();
        $check = $this->matchTransit($firstPackage, $packages);

        foreach ($packages as $package) {
            if (!is_null($package->deliveryRoutes)) {
                $variant = 1; // to set this variant is any routes
            } else {
                $variant = 2; // to set this variant cant have routes
            }

            array_push($allVariant, $variant);
        }

        if ($check) {
            return (new Response(Response::RC_SUCCESS))->json();
        } else {
            if (!in_array(1, $allVariant)) {
                return (new Response(Response::RC_SUCCESS))->json();
            } elseif (in_array(1, $allVariant) && in_array(2, $allVariant)) {
                return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Resi tidak dapat di proses, silahkan pili resi yang lain']))->json();
            } else {
                return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Resi tidak dapat di proses, silahkan pili resi yang lain']))->json();
            }
        }
    }

    public function matchTransit($firstPackage, $packages): bool
    {
        $lastPackage = $packages->skip(1);
        $checkDestination = Route::checkDestinationCityTransit($firstPackage, $lastPackage);

        return $checkDestination;
    }

    public function partnerWithOutRoute(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['nullable', 'exists:partners,code']
        ]);

        $partners = Partner::query()->whereIn('type', [Partner::TYPE_BUSINESS, Partner::TYPE_POOL]);

        if ($request->code) {
            $partners->where('code', 'ilike', '%' . $request->code . '%');
        }

        return (new Response(Response::RC_SUCCESS, $partners->paginate(5)))->json();
    }

    /**
     * Get Transit Packages
     */
    public function getPackagesTransit($package, $partnerId)
    {
        $package = $package->filter(function ($q) use ($partnerId) {
            $partnerIdFromDeliveries = $q->deliveries->last()->partner_id;
            if (!is_null($q->deliveryRoutes)) {
                $partnerDooringId = $q->deliveryRoutes->partner_dooring_id;
                $partnerDooring = Partner::query()->where('id', $partnerDooringId)->first();

                if (!is_null($partnerDooring) && $partnerDooring->type === Partner::TYPE_TRANSPORTER) {
                    return false;
                }

                if ($partnerIdFromDeliveries === $partnerId &&  $partnerDooringId !== $partnerId) {
                    return true;
                }
            } else {
                if ($q->deliveries->count() === 1) {
                    $partner = Partner::query()->where('id', $partnerId)->first();
                    $routes = Route::getWarehousePartner($partner->code, $q);
                    $isDirectDooring = Route::checkDirectDooring($partner, $routes);
                    if ($isDirectDooring) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    $type = 'transit';
                    $delivery = $q->deliveries->last();
                    $isDooring = Route::checkDooring($q, $delivery, $type);
                    if (!$isDooring && $partnerIdFromDeliveries === $partnerId) {
                        return true;
                    }
                }
            }

            return false;
        })->values();

        return $package;
    }

    /**
     * Get Dooring Packages
     */
    public function getPackagesDooring($package, $partnerId)
    {
        $package = $package->filter(function ($q) use ($partnerId) {
            $partnerIdFromDeliveries = $q->deliveries->last()->partner_id;
            if (!is_null($q->deliveryRoutes)) {
                $partnerDooringId = $q->deliveryRoutes->partner_dooring_id;
                if ($partnerIdFromDeliveries === $partnerId &&  $partnerDooringId === $partnerId) {
                    return true;
                } elseif (Route::checkVendorDooring($q->deliveryRoutes)) {
                    return true;
                } else {
                    $type = 'dooring';
                    $delivery = $q->deliveries->last();
                    $isDooringFromRoute = Route::checkDooring($q, $delivery, $type);
                    if ($isDooringFromRoute && $partnerDooringId === $partnerId) {
                        return true;
                    }
                }
            } else {
                if ($q->deliveries->count() === 1) {
                    $partner = Partner::query()->where('id', $partnerId)->first();
                    $routes = Route::getWarehousePartner($partner->code, $q);
                    $isDirectDooring = Route::checkDirectDooring($partner, $routes);
                    if ($isDirectDooring) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $type = 'dooring';
                    $delivery = $q->deliveries->last();
                    $isDooring = Route::checkDooring($q, $delivery, $type);
                    if ($partnerIdFromDeliveries !== $partnerId) {
                        return false;
                    } else {
                        if ($isDooring) {
                            return true;
                        }
                    }
                }
            }

            return false;
        })->values();

        return $package;
    }
}
