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

    public function pending(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);


            $this->getResource();
            $this->query->when($request->input('q'), function (Builder $query, $q) {
                $query->where('barcode', 'LIKE', '%'.$q.'%');
            });

            $this->query->where('payment_status', Package::PAYMENT_STATUS_PENDING)->where('status', Package::STATUS_ACCEPTED);

            $this->query->orderBy('payment_status');
            $this->query->with(['customer', 'items', 'attachments']);

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))));
        }

        return view('admin.master.history.pending.index');
    }

    public function paid(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            $this->query->with(['customer', 'items', 'attachments']);
            $this->getResource();
            $this->query->paid();
            // $this->query = $this->query->whereIn('payment_status', ['paid', 'pending']);
            $this->query->orderBy('payment_status');
            $this->query->when($request->input('q'), function (Builder $query, $q) {
                $query->where('barcode', 'LIKE', '%'.$q.'%');
            });

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))));
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
            $this->attributes = $request->validate($this->rules);

            $this->getResource();
            $this->query = $this->query->failed();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))));
        }

        return view('admin.master.history.cancel.index');
    }
}
