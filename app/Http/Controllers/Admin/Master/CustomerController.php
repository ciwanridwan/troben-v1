<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Customers\DeleteExistingCustomer;
use App\Http\Resources\Admin\MasterCustomerResource;
use Illuminate\Database\Eloquent\Model;

class CustomerController extends Controller
{
    use HasResource, DispatchesJobs;
    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var string
     */
    protected string $model = Customer::class;

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
        $this->baseBuilder(Customer::query());
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

            $this->withSumAndCount();

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

    /**
     *
     *  Delete Customer
     * Route Path       : admin/master/customer
     * Route Name       : admin.master.customer
     * Route Method     : DELETE.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $customer = (new Customer)->byHashOrFail($request->hash);
        $job = new DeleteExistingCustomer($customer);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->customer))->json();
    }

    public function withSumAndCount(): Builder
    {
        $this->query = $this->query->withCount([
            'orders as orderCount' => function ($query) {
                $query->paid();
            },
            'orders as orderTotalPayment' => function ($query) {
                $query->select(DB::raw('SUM(total_payment)'));
            },
        ]);
        return $this->query;
    }
}
