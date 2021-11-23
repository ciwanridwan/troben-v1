<?php

namespace App\Http\Controllers\Api\Promote;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Package\PackageResource;
use App\Http\Resources\PriceResource;
use App\Http\Resources\Promote\DataDiscountResource;
use App\Http\Resources\Promote\PromotionResource;
use App\Http\Response;
use App\Jobs\Promo\CreateNewPromotion;
use App\Jobs\Promo\UploadFilePromotion;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Promos\ClaimedPromotion;
use App\Models\Promos\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;
    /**
     * Get Type of Promo List
     * Route Path       : {API_DOMAIN}/promo
     * Route Name       : api.promo.
     */

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->getBasicBuilder(Promotion::query());
        $query->where('is_active', true);
        $query->get();

        return $this->jsonSuccess(PromotionResource::collection($query->paginate(request('per_page', 15))));
    }

    public function show(Promotion $promotion, Package $package) :JsonResponse
    {
        if ($package->transporter_type == $promotion->transporter_type){
            $is_insured = 0;
            foreach ($package->items as $item){
                if ($item->is_insured == true){
                    $is_insured++;
                }
            }
            if ($is_insured == count($package->items)){
                if($package->destination_regency_id == $promotion->destination_regency_id){
                    $promotion->is_available = true;
                    return (new Response(Response::RC_SUCCESS, $promotion))->json();
                }
            }
        }
        $promotion->is_available = false;
        return (new Response(Response::RC_SUCCESS, $promotion))->json();
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(
            request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('title', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required',
            'terms_and_conditions' => 'required',
            'transporter_type' => 'nullable',
            'destination_regency_id' => 'required',
            'min_payment' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'attachment' => 'nullable',
        ]);
        $job = new CreateNewPromotion($request->all());
        $this->dispatchNow($job);

        $job = new UploadFilePromotion($job->promotion, $request->file('attachment') ?? []);
        $this->dispatchNow($job);

        return $this->jsonSuccess(PromotionResource::make($job->promotion));
    }

    public function calculate(Request $request, Package $package): JsonResponse
    {
        $request->validate([
            'promotion_hash' => ['nullable']
        ]);
        $this->authorize('view', $package);
        $check = ClaimedPromotion::where('customer_id', $package->customer_id)->latest()->first();
        switch ($check){
            case null :
                $data = $this->calculation($request->promotion_hash, $package);
                $collection = collect($data);
                $collection->push($package);


                return $this->jsonSuccess(DataDiscountResource::make(array_merge($data,$package->toArray())));

            default:
                if ($request->promotion_hash != null){
                    if ($check->updated_at < $check->updated_at->addDays(1)){

                        $data = $this->calculation($request->promotion_hash, $package);
                        return (new Response(Response::RC_SUCCESS, $data))->json();
                    }
                }
        }
        return (new Response(Response::RC_BAD_REQUEST))->json();
    }

    public function calculation($promotion_hash, Package $package)
    {
        $promotion = Promotion::byHashOrFail($promotion_hash);
        $service = $package->prices->where('type', Price::TYPE_SERVICE)->first();
        if ($package->total_weight < $promotion->min_weight){
            $discount = $service->amount * 0;
        }else{
            $discount = $service->amount - ($package->tier_price * $promotion->min_weight);
        }
        $data = [
                'service_price' => $service->amount,
                'discount' => $discount,
                'total_payment' => $package->total_amount - $discount
        ];

        return $data;
    }
}
