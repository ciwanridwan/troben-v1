<?php

namespace App\Http\Controllers\Api\Promote;

use App\Http\Controllers\Controller;
use App\Http\Resources\Promote\PromotionResource;
use App\Jobs\Promo\CreateNewPromotion;
use App\Jobs\Promo\UploadFilePromotion;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $this->attributes = Validator::make($request->all(), [
            'type' => 'required',
        ])->validate();

        $query = $this->getBasicBuilder(Promotion::query());
        $query->where('is_active', true);
        $query->when(request()->has('type'), fn ($q) => $q->where('type', $this->attributes['type']));

        return $this->jsonSuccess(PromotionResource::collection($query->paginate(request('per_page', 15))));
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
            fn ($q) => $q->where('name', 'like', '%'.$this->attributes['q'].'%')
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
            'min_payment' => 'required',
            'max_payment' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'attachment' => 'nullable',
        ]);
        $inputs = $request->all();
        $job = new CreateNewPromotion($inputs);
        $this->dispatchNow($job);

        $job = new UploadFilePromotion($job->promotion, $request->file('attachment') ?? []);
        $this->dispatchNow($job);

        return $this->jsonSuccess(PromotionResource::make($job->promotion));
    }
}
