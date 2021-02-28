<?php

namespace App\Http\Controllers\Admin\Master;

use App\Models\Price;
use App\Http\Response;
use App\Models\Service;
use App\Models\Geo\Regency;
use App\Models\Geo\District;
use Illuminate\Http\Request;
use App\Jobs\Price\CreateNewPrice;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
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
            ['name'],
            ['zip_code'],
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
            // dd(PriceResource::collection($this->query->paginate(request('per_page', 15)))->toArray($request));
            $data = [
                'resource' => PriceResource::collection($this->query->paginate(request('per_page', 15))),
            ];
            $data = array_merge($data, $this->extraData());


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
        $job = new CreateNewPrice($request->all());
        $this->dispatch($job);

        return $this->jsonSuccess(PriceResource::make($job->price));
    }




    public function extraData()
    {
        return [
            'regencies' => Regency::all(),
            'districts' => District::all(),
            'services' => Service::all(),
        ];
    }
}
