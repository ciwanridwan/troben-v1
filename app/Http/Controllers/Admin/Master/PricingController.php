<?php

namespace App\Http\Controllers\Admin\Master;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Http\Response;
use App\Models\Geo\District;
use App\Models\Geo\Regency;
use App\Models\Price;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
     * Showing page and get all partner data.
     * Route Path       : {APP_URL}/admin/master/partner
     * Route Name       : admin.master.partner
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
                'resource' => PriceResource::collection($this->query->paginate(request('per_page', 15)))
            ];
            $data = array_merge($data, $this->extraData());


            return (new Response(Response::RC_SUCCESS, $data))->json();
        }

        return view('admin.master.pricing.district');
    }
    public function extraData()
    {
        return [
            'regencies' => Regency::all(),
            'districts' => District::all(),
            'services' => Service::all()
        ];
    }
}
