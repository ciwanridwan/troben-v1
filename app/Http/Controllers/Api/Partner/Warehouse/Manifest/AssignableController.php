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
                    if (! is_null($package->deliveryRoutes)) {
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

        //if (is_null($partnerCode)) {
        //    return (new Response(Response::RC_DATA_NOT_FOUND, ['Message' => 'Mitra Tujuan Belum Tersedia']))->json();
        //}
        $query = Partner::query()->where('id', '!=', $repository->getPartner()->id);

        if ($partnerCode === 'all' || is_null($partnerCode)) {
            $query->whereIn('type', [Partner::TYPE_BUSINESS, Partner::TYPE_POOL]);
        } else {
            $query->whereIn('code', $partnerCode);
        }

        $query->when(
            $request->input('code'),
            fn (Builder $builder, $code) => $builder->Where('code', 'LIKE', '%'.$code.'%')
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
        $query = $repository->queries()->getPackagesQuery();

        if ($request->has('q')) {
            $id = Code::select('codeable_id')
                ->where('content', 'like', '%'.$request->q.'%')
                ->pluck('codeable_id');
            if ($id->count() == 0) {
                return (new Response(Response::RC_DATA_NOT_FOUND))->json();
            }
            $query->whereIn('id', $id);
        }

        $query->whereIn('status', [Package::STATUS_PACKED, Package::STATUS_IN_TRANSIT]);
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

        $query->with('estimator', 'packager', 'items', 'partner_performance');
        $result = $query->paginate($request->input('per_page'));

        return $this->jsonSuccess(PackageResource::collection($query->paginate($request->input('per_page'))), null, true);
        // return $this->jsonSuccess(PackageResourceDeprecated::collection($query->paginate($request->input('per_page'))), null, true);
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
        $firstPackage = $packages->first();
        $check = $this->matchTransit($firstPackage, $packages);

        foreach ($packages as $package) {
            if (! is_null($package->deliveryRoutes)) {
                $variant = 1; // to set this variant is any routes
            } else {
                $variant = 2; // to set this variant cant have routes
            }

            array_push($allVariant, $variant);
        }

        if ($check) {
            return (new Response(Response::RC_SUCCESS))->json();
        } else {
            if (! in_array(1, $allVariant)) {
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
            $partners->where('code', 'ilike', '%'.$request->code.'%');
        }

        return (new Response(Response::RC_SUCCESS, $partners->paginate(5)))->json();
    }
}
