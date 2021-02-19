<?php

namespace App\Http\Controllers\Api\WebAdminResource\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebAdminResource\Master\CustomerResource;
use App\Models\Customers\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerController extends Controller
{

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var Builder
     */
    protected Builder $query;

    protected array $rules;

    function __construct()
    {
        $this->rules = [
            'name' => ['filled'],
            'email' => ['filled'],
            'phone' => ['filled'],
            'q' => ['filled']
        ];
        $this->baseBuilder();
    }

    /**
     *
     * Get All Customer Account
     * Route Path       : {API_DOMAIN}/account/customer
     * Route Name       : api.account.customer
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {

        $this->attributes = $request->validate($this->rules);

        foreach (Arr::except($this->attributes, 'q') as $key => $value) {
            $this->getByColumn($key);
        }
        if (Arr::has($this->attributes, 'q')) {
            $this->getSearch($this->attributes['q']);
        }

        return $this->jsonSuccess(CustomerResource::collection($this->query->paginate(request('per_page', 15))));
    }

    public function getByColumn($column = ''): Builder
    {
        $this->query = $this->query->where($column, 'LIKE', "%" . $this->attributes[$column] . "%");
        return $this->query;
    }

    public function getSearch($q = '')
    {
        $columns = Arr::except($this->rules, 'q');

        // first
        $key_first = array_key_first($columns);
        $this->query = $this->query->where($key_first, 'LIKE', "%" . $q . "%");

        foreach (Arr::except($columns, $key_first) as $key => $value) {
            $this->query = $this->query->orWhere($key, 'LIKE', "%" . $q . "%");
        }

        return $this->query;
    }

    public function baseBuilder(): Builder
    {
        return $this->query = Customer::query();
    }
}
