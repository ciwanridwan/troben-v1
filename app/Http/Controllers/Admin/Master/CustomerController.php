<?php

namespace App\Http\Controllers\Admin\Master;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MasterCustomerResource;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * @var array
     */
    protected array $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => ['filled'],
            'email' => ['filled'],
            'phone' => ['filled'],
            'q' => ['filled'],
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            foreach (Arr::except($this->attributes, 'q') as $key => $value) {
                $this->getByColumn($key);
            }
            if (Arr::has($this->attributes, 'q')) {
                $this->getSearch($this->attributes['q']);
            }
            return $this->jsonSuccess(MasterCustomerResource::collection($this->query->paginate(request('per_page', 15))));
        }

        return view('admin.master.customer.index');
    }

    public function getByColumn($column = ''): Builder
    {
        $this->query = $this->query->where($column, 'LIKE', '%' . $this->attributes[$column] . '%');

        return $this->query;
    }

    public function getSearch($q = '')
    {
        $columns = Arr::except($this->rules, 'q');

        // first
        $key_first = array_key_first($columns);
        $this->query = $this->query->where($key_first, 'LIKE', '%' . $q . '%');

        foreach (Arr::except($columns, $key_first) as $key => $value) {
            $this->query = $this->query->orWhere($key, 'LIKE', '%' . $q . '%');
        }

        return $this->query;
    }

    public function baseBuilder(): Builder
    {
        return $this->query = Customer::query()->withCount(['orders as orderCount' => function ($query) {
            $query->paid();
        }]);
    }
}
