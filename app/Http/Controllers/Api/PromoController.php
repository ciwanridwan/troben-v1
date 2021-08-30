<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoResource;
use App\Jobs\Promo\CreateNewPromo;
use App\Jobs\Promo\UploadFilePromo;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected $attributes;
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
            'type' => 'nullable',
        ])->validate();

        $query = $this->getBasicBuilder(Promo::query());

        $query->when(request()->has('type'), fn ($q) => $q->where('type', $this->attributes['type']));

        return $this->jsonSuccess(PromoResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => 'required',
        ]);

        $inputs = $request->all();

        $job = new CreateNewPromo($inputs);
        $this->dispatchNow($job);

        $job = new UploadFilePromo($job->promo, $request->file('cover') ?? []);
        $this->dispatchNow($job);

        return $this->jsonSuccess(PromoResource::make($job->promo));
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
