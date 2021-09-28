<?php

namespace App\Http\Controllers\Api\Courier;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Courier\DeliveryResource;
use App\Http\Resources\Api\Delivery\DeliveryPassedResource;
use App\Http\Response;
use App\Jobs\Kurir\KurirRejectDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use HasResource;

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
    protected string $model = Package::class;


    /**
     * @var array
     */
    protected array $rules = [
        'q' => ['nullable'],
    ];

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

    public function rejectHistory(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getDeliveriesRejectCourierQuery();

        $query->with(['packages.origin_district', 'packages.origin_sub_district', 'packages.destination_sub_district', 'packages.code']);

        $query->orderByDesc('created_at');

        return $this->jsonSuccess(DeliveryPassedResource::collection($query->paginate($request->input('per_page', 15))));
    }

    public function reject(Delivery $delivery): JsonResponse
    {
        $job = new KurirRejectDelivery($delivery);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->rejected))->json();
    }

    public function accept(Delivery $delivery): JsonResponse
    {
        $delivery->status = Delivery::STATUS_ACCEPTED;
        $delivery->save();

        return (new Response(Response::RC_SUCCESS, $delivery))->json();
    }

    /**
     * Get Basic Builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(
            request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('name', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }
}
