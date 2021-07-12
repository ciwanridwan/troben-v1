<?php

namespace App\Http\Controllers\Api;

use App\Models\Geo\Country;
use App\Models\Geo\Regency;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use Illuminate\Http\Request;
use App\Models\Geo\SubDistrict;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Geo\CountryResource;
use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\ProvinceResource;
use App\Http\Resources\Geo\SubDistrictResource;

class GeoController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Get list of geographical information
     * Route Path       : {API_DOMAIN}/geo
     * Route Method     : GET
     * Route Name       : api.geo.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => ['required', Rule::in([
                'country', 'province', 'regency', 'district', 'sub_district',
            ])],
            'q' => 'string|nullable',
            'country_id' => 'nullable',
            'province_id' => 'nullable',
            'regency_id' => 'nullable',
            'district_id' => 'nullable',
            'zip_code' => 'nullable',
            'id' => 'nullable',
        ])->validate();

        switch ($this->attributes['type']) {
            case 'country':
                return $this->getCountries();
            case 'province':
                return $this->getProvinces();
            case 'regency':
                return $this->getRegencies();
            case 'district':
                return $this->getDistricts();
            case 'sub_district':
                return $this->getSubDistricts();
        }
    }

    /**
     * Get list countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getCountries(): JsonResponse
    {
        $query = $this->getBasicBuilder(Country::query());

        return $this->jsonSuccess(CountryResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * Get list of provinces.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getProvinces(): JsonResponse
    {
        $query = $this->getBasicBuilder(Province::query()->with('country'));
        $query->when(request()->has('country_id'), fn ($q) => $q->where('country_id', $this->attributes['country_id']));

        return $this->jsonSuccess(ProvinceResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * Get Regencies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getRegencies(): JsonResponse
    {
        $query = $this->getBasicBuilder(Regency::query()->with(['province', 'country']));
        $query->when(request()->has('country_id'), fn ($q) => $q->where('country_id', $this->attributes['country_id']));
        $query->when(request()->has('province_id'), fn ($q) => $q->where('province_id', $this->attributes['province_id']));
        $query->when(request()->input('origin') == '1', fn ($q) => $q->where('name', 'Kota Adm. Jakarta Selatan'));

        return $this->jsonSuccess(RegencyResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * Get list of districts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getDistricts(): JsonResponse
    {
        $query = $this->getBasicBuilder(District::query()->with(['country', 'province', 'regency']));

        $query->when(request()->has('country_id'), fn ($q) => $q->where('country_id', $this->attributes['country_id']));
        $query->when(request()->has('province_id'), fn ($q) => $q->where('province_id', $this->attributes['province_id']));
        $query->when(request()->has('regency_id'), fn ($q) => $q->where('regency_id', $this->attributes['regency_id']));

        return $this->jsonSuccess(DistrictResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * Get list of sub districts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getSubDistricts(): JsonResponse
    {
        $query = $this->getBasicBuilder(SubDistrict::query()->with(['country', 'province', 'regency', 'district']));

        $query->when(request()->has('country_id'), fn ($q) => $q->where('country_id', $this->attributes['country_id']));
        $query->when(request()->has('province_id'), fn ($q) => $q->where('province_id', $this->attributes['province_id']));
        $query->when(request()->has('regency_id'), fn ($q) => $q->where('regency_id', $this->attributes['regency_id']));
        $query->when(request()->has('district_id'), fn ($q) => $q->where('district_id', $this->attributes['district_id']));

        $query->when(request()->has('zip_code'), fn ($q) => $q->where('zip_code', 'like', '%'.$this->attributes['zip_code'].'%'));

        return $this->jsonSuccess(SubDistrictResource::collection($query->paginate(request('per_page', 15))));
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
