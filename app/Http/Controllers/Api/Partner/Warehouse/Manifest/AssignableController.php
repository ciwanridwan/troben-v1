<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

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
use App\Http\Resources\Api\Package\PackageResource;
use App\Http\Resources\Admin\Master\PartnerResource;
use App\Http\Resources\Api\Transporter\TransporterDriverResource;

class AssignableController extends Controller
{
    public function partner(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = Partner::query()->where('id', '!=', $repository->getPartner()->id);

        $query->when(
            $request->input('type'),
            fn (Builder $builder, $type) => $builder->whereIn('type', Arr::wrap($type)),
            fn (Builder $builder, $type) => $builder->whereIn('type', [
                Partner::TYPE_BUSINESS,
                Partner::TYPE_SPACE,
                Partner::TYPE_POOL,
            ])
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

        $query->with('estimator', 'packager', 'items');

        return $this->jsonSuccess(PackageResource::collection($query->paginate($request->input('per_page'))));
    }
}
