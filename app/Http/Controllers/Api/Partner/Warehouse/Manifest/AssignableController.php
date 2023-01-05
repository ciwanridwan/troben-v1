<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Actions\Deliveries\Route;
use App\Http\Response;
use App\Models\Code;
use Illuminate\Support\Arr;
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
use App\Http\Resources\Api\Assignable\PackageResource;
use App\Http\Resources\Api\Package\PackageResourceDeprecated;
use App\Http\Resources\Api\Transporter\TransporterDriverResource;

class AssignableController extends Controller
{
    // public function partner(Request $request, PartnerRepository $repository): JsonResponse
    // {
    //     $query = Partner::query()->where('id', '!=', $repository->getPartner()->id);

    //     $query->when(
    //         $request->input('type'),
    //         fn (Builder $builder, $type) => $builder->whereIn('type', Arr::wrap($type)),
    //         fn (Builder $builder, $type) => $builder->whereIn('type', [
    //             Partner::TYPE_BUSINESS,
    //             Partner::TYPE_SPACE,
    //             Partner::TYPE_POOL,
    //         ])
    //     );

    //     $query->when(
    //         $request->input('code'),
    //         fn (Builder $builder, $code) => $builder->Where('code', 'LIKE', '%'.$code.'%')
    //     );

    //     return $this->jsonSuccess(PartnerResource::collection($query->paginate($request->input('per_page'))));
    // }

    public function partner(Request $request, PartnerRepository $repository, Delivery $delivery): JsonResponse
    {
        $query = Partner::query()->where('id', '!=', $repository->getPartner()->id);
        $packages = $delivery->packages()->get();

        foreach ($packages as $package) {
            $partnerCode = Route::setPartners($package->deliveryRoutes);
            $query->where('code', $partnerCode);
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

        return $this->jsonSuccess(TransporterDriverResource::collection($query->paginate($request->input('per_page'))));
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
            $query->whereIn('id', $id)->get();
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
}
