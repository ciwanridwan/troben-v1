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
use Illuminate\Validation\Rules\Exists;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class AssignableController extends Controller
{
    public function partner(Request $request, PartnerRepository $repository): JsonResponse
    {
        $partnerCode = null;

        $setPartner = Route::checkPackages($request->all());
        if ($setPartner) {
            $packages = Route::getPackages($request->all());
            $partnerByRoutes = [];
            foreach ($packages as $package) {
                $partnerByRoute = Route::setPartners($package->deliveryRoutes);
                array_push($partnerByRoutes, $partnerByRoute);
            }
            $partnerCode = $partnerByRoutes;
        } else {
            $partnerCode = Route::generate($repository->getPartner(), $request->all());
        }

        if (is_null($partnerCode)) {
            return (new Response(Response::RC_DATA_NOT_FOUND, ['Message' => 'Mitra Tujuan Belum Tersedia']))->json();
        }

        $query = Partner::query()->where('id', '!=', $repository->getPartner()->id);
        $query->whereIn('code', $partnerCode);
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
        $query = $repository->queries()->getPackagesQuery();

        if ($request->has('q')) {
            $id = Code::select('codeable_id')
                ->where('content', 'like', '%' . $request->q . '%')
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

        $packages = Code::query()->whereIn('content', $request->package_code)->get();
        $check = null;
        $variant = 0;
        $allVariant = [];
        $firstPackage = $packages->first();

        foreach ($packages as $package) {
            if (is_null($package->deliveryRoutes)) {
                $variant = 1;
            } else {
                $route = $package->deliveryRoutes;
            }
        }
    }
}
