<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Events\Packages\PackagePaymentVerified;
use Illuminate\Http\JsonResponse;

class HistoryController extends Controller
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

    protected array $byRelation = [
        'customer' => [],

    ];

    /**
     * @var array
     */
    protected array $rules = [
        'q' => ['nullable'],
    ];

    public function __construct()
    {
        $this->baseBuilder();
    }

    public function index(Request $request)
    {
        return view('admin.master.history.index');
    }

    public function getHistoryDataByPackageStatus(Request $request, $status_condition): JsonResponse
    {
        if ($request->has('partner')) {
            return $this->getPartners($request);
        }
        $this->query->whereHas('code', function ($query) use ($request) {
            $query->whereRaw("LOWER(content) like '%".strtolower($request->q)."%'");
        });

        $this->query->where($status_condition);
        $this->query->with(['items', 'items.prices', 'deliveries', 'deliveries.partner', 'code']);
        $this->query->orderBy('status');
        // $this->query->whereDoesntHave('deliveries');

        return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
    }

    public function pending(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->getHistoryDataByPackageStatus(
                $request,
                [
                    ['status', Package::STATUS_ACCEPTED],
                    ['payment_status', Package::PAYMENT_STATUS_PENDING]
                ]
            );
        }


        return view('admin.master.history.pending.index');
    }

    public function paid(Request $request)
    {
        // dd(Package::with(['items', 'items.prices'])->get()->toArray());
        if ($request->expectsJson()) {
            return $this->getHistoryDataByPackageStatus(
                $request,
                [
                    ['payment_status', Package::PAYMENT_STATUS_PAID]
                ]
            );
        }

        return view('admin.master.history.paid.index');
    }

    public function paymentVerifed(Package $package)
    {
        $event = new PackagePaymentVerified($package);
        event($event);

        return (new Response(Response::RC_SUCCESS, $event->package));
    }

    public function cancel(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->getHistoryDataByPackageStatus(
                $request,
                [
                    ['status', Package::STATUS_CANCEL]
                ]
            );
        }

        return view('admin.master.history.cancel.index');
    }
}
