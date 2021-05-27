<?php

namespace App\Http\Controllers\Admin;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use App\Events\Packages\PackageCanceledByAdmin;
use App\Events\Packages\PackagePaymentVerified;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;

class HomeController extends Controller
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
    protected string $model = Package::class;

    /**
     * @var array
     */
    protected array $rules;

    public function __construct()
    {
        $this->rules = [
            'q' => ['nullable'],
        ];

        $this->baseBuilder();
    }

    public function getSearch(Request $request)
    {
        $this->query = $this->query->search($request->q);
        $this->query->orWhereHas('code', function ($query) use ($request) {
            $query->search($request->q);
        });
        return $this;
    }

    public function dataRelation()
    {
        $this->query->with(['items', 'items.prices', 'prices', 'deliveries', 'deliveries.partner', 'code']);
        $this->query->orderBy('status');

        return $this;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return $this->getPartners($request);
            }

            $this->getSearch($request);

            // dont show canceled order
            $this->query->where('status', '!=', Package::STATUS_CANCEL);
            $this->dataRelation($request);


            // $this->query->whereDoesntHave('deliveries');

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.home.index');
    }

    public function receipt(Request $request)
    {
        if ($request->expectsJson()) {
            $this->getSearch($request);
            $this->dataRelation($request);

            // $this->query->whereDoesntHave('deliveries');

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }
        return view('admin.home.receipt.index');
    }


    public function orderAssignation(Package $package, Partner $partner): JsonResponse
    {
        $job = new AssignFirstPartnerToPackage($package, $partner);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->package))->json();
    }
    public function paymentConfirm(Package $package)
    {
        event(new PackagePaymentVerified($package));
        return (new Response(Response::RC_SUCCESS, $package->refresh()))->json();
    }

    public function cancel(Package $package)
    {
        event(new PackageCanceledByAdmin($package));
        return (new Response(Response::RC_SUCCESS, $package->refresh()))->json();
    }

    private function getPartners(Request $request): JsonResponse
    {
        $this->query = Partner::query()->whereHas('transporters', function ($query) use ($request) {
            $query->where('type', $request->transporter_type);
        })->search($request->q);

        return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
    }
}
