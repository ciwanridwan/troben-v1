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
use App\Jobs\Partners\Transporter\UpdateExistingTransporter;

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
    protected array $rules = [
        'registration_name' => ['filled'],
        'registration_number' => ['filled'],
        'type' => ['filled'],
        'q' => ['nullable'],
    ];

    public function __construct()
    {
        $this->baseBuilder();
        $this->query =  $this->query->whereHas('partner')->where('verified_at', NULL);
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

            $this->getResource();

            return (new Response(Response::RC_SUCCESS, TransporterResource::collection($this->query->paginate(request('per_page', 15)))))->json();
        }

        return view('admin.master.transporter.index');
    }

    public function update(Request $request)
    {
        $transporter = (new Transporter())->byHash($request->hash);
        $request->validate([
            'is_verified' => ['required', 'boolean']
        ]);
        $job = new UpdateExistingTransporter($transporter, $request->all());
        $this->dispatch($job);
        return (new Response(Response::RC_SUCCESS, $job->transporter))->json();
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
