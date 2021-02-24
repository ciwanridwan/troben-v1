<?php

namespace App\Http\Controllers\Admin\Master;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Models\Price;
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
    protected array $rules;

    public function __construct()
    {
        $attributes = (new Price())->getTableColumns();
        $this->rules = [
            'q'             => ['filled'],
        ];
        foreach ($attributes as $value) {
            $this->rules[$value] = ['filled'];
        }
        $this->baseBuilder(Price::query());
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

            foreach (Arr::except($this->attributes, ['q']) as $key => $value) {
                $this->getByColumn($key);
            }
            if (Arr::has($this->attributes, 'q')) {
                $this->getSearch($this->attributes['q']);
            }

            return $this->jsonSuccess(PriceResource::collection($this->query->paginate($request->input('per_page', 15))));
        }

        return view('admin.master.pricing.district');
    }
}
