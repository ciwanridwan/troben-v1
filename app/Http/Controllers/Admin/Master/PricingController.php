<?php

namespace App\Http\Controllers\Admin\Master;

use App\Models\Price;
use App\Http\Response;
use App\Models\Service;
use App\Exceptions\Error;
use App\Models\Geo\Regency;
use App\Models\Geo\District;
use Illuminate\Http\Request;
use App\Models\Geo\SubDistrict;
use App\Jobs\Price\CreateNewPrice;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Jobs\Price\DeleteExistingPrice;
use App\Jobs\Price\UpdateExistingPrice;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;

class PricingController extends Controller
{
    use HasResource;


    /**
     * Filtered attributes.
     * @var array
     */
    protected array $attributes;

    /**
     * Price base query builder.
     * @var Builder
     */
    protected Builder $query;

    protected string $model = Price::class;

    /**
     * Request rule definitions.
     * @var array
     */
    protected array $rules = [
        'q' => ['nullable'],
    ];

    protected array $byRelation = [
        'service' => [
            ['name'],
        ],
        'district' => [
            ['name'],
        ],
        'province' => [
            ['name'],
        ],
        'regency' => [
            ['name'],
        ],
        'destination' => [
            ['name'], ['zip_code'],
        ],
    ];

    public function __construct()
    {
        $this->baseBuilder();
    }


    /**
     * Showing page and get all pricing data.
     * Route Path       : {APP_URL}/admin/master/pricing
     * Route Name       : admin.master.pricing
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            $this->getResource();

            $data = [
                'resource' => PriceResource::collection($this->query->paginate(request('per_page', 15))),
            ];
            /*$data = array_merge($data, $this->extraData());*/

            return (new Response(Response::RC_SUCCESS, $data))->json();
        }

        return view('admin.master.pricing.district');
    }


    /**
     * Showing page and get all pricing data.
     * Route Path       : {APP_URL}/admin/master/pricing/district
     * Route Name       : admin.master.pricing/district
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function store(Request $request)
    {
        $inputs = $this->prepareAllSubDistrict($request);
        foreach ($inputs as $key => $value) {
            $job = new CreateNewPrice($inputs);
            $this->dispatch($job);
        }

        return $this->jsonSuccess(PriceResource::make($job->price));
    }

    public function prepareAllSubDistrict(Request $request)
    {
        // insert all of sub district
        $district = District::find($request->destination_district_id);
        throw_if($district === null, Error::make(Response::RC_INVALID_DATA));
        $price_input = $request->all();
        $price_inputs = [];
        foreach ($district->sub_districts as $key => $sub_district) {
            $price_inputs[$key] = $price_input;
            $price_inputs[$key]['destination_id'] = $sub_district->id;
        }

        return $price_inputs;
    }

    /**
     * Delete Pricing.
     * Route Path       : {APP_URL}/admin/master/pricing/district
     * Route Name       : admin.master.pricing/district
     * Route Method     : DELETE.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function destroy(Request $request)
    {
        $price = (new Price())->byHashOrFail($request->hash);
        $job = new DeleteExistingPrice($price);
        $this->dispatch($job);

        return $this->jsonSuccess(PriceResource::make($job->price));
    }

    /**
     * Delete Pricing.
     * Route Path       : {APP_URL}/admin/master/pricing/district
     * Route Name       : admin.master.pricing/district
     * Route Method     : DELETE.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function update(Request $request)
    {
        $price = (new Price())->byHashOrFail($request->hash);
        $job = new UpdateExistingPrice($price, $request->all());
        $this->dispatch($job);

        return $this->jsonSuccess(PriceResource::make($job->price));
    }




    public function extraData()
    {
        return [
            'regencies' => Regency::all(),
            'districts' => District::all(),
            'sub_districts' => SubDistrict::all(),
            'services' => Service::all(),
        ];
    }
}
