<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Models\Partners\Transporter;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Resources\Admin\Master\TransporterResource;
use App\Jobs\Partners\Transporter\DeleteExistingTransporter;

class TransporterController extends Controller
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
    protected string $model = Transporter::class;

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
            'q' => ['nullable'],
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

            return $this->jsonSuccess(TransporterResource::collection($this->query->paginate(request('per_page', 15))));
        }

        return view('admin.master.transporter.index');
    }

    /**
     *
     *  Delete Transporter
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
        $transporter = (new Transporter)->byHashOrFail($request->hash);
        $job = new DeleteExistingTransporter($transporter);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->customer))->json();
    }
}