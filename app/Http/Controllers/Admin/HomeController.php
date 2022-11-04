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
use App\Jobs\Codes\Logs\CreateNewLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Models\Code;
use App\Models\CodeLogable;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

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
        $this->query->with(
            [
                'items', 'prices', 'payments', 'items.prices', 'origin_regency',
                'origin_district', 'origin_sub_district', 'destination_regency', 'destination_district',
                'destination_sub_district', 'deliveries', 'deliveries.partner', 'code', 'attachments', 'motoBikes','canceled'
            ]
        );
        // $this->query->orderBy('status','desc');

        return $this;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return $this->getPartners($request);
            }

            $this->getSearch($request);

            $this->dataRelation($request);
            $this->query->orderBy('created_at', 'desc');

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.home.index');
    }

    public function accountExecutive(Request $request)
    {
        return view('admin.home.account-executive.index');
    }

    public function teamAgent(Request $request)
    {
        return view('admin.home.account-executive.team-agent');
    }

    public function teamDetail(Request $request)
    {
        return view('admin.home.account-executive.team-detail');
    }

    public function trawlbensCorporate(Request $request)
    {
        return view('admin.home.form-register.trawlbens-corporate');
    }

    public function mitraBisnis(Request $request)
    {
        return view('admin.home.form-register.mitra-bisnis');
    }

    public function mitraSpace(Request $request)
    {
        return view('admin.home.form-register.mitra-space');
    }

    public function mitraPos(Request $request)
    {
        return view('admin.home.form-register.mitra-pos');
    }

    public function mitraPoolWarehouse(Request $request)
    {
        return view('admin.home.form-register.mitra-pool-warehouse');
    }

    public function mitraKurirMotor(Request $request)
    {
        return view('admin.home.form-register.mitra-kurir-motor');
    }

    public function mitraKurirMobil(Request $request)
    {
        return view('admin.home.form-register.mitra-kurir-mobil');
    }

    public function receipt(Request $request)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return $this->getPartners($request, false);
            }

            $this->getSearch($request);
            $this->dataRelation($request);
            $this->query->with(['code.logs', 'deliveries.code']);
            $this->query->orderBy('created_at', 'desc');
            // $this->query->whereDoesntHave('deliveries');

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }
        return view('admin.home.receipt.index');
    }

    public function storeLog(Request $request, Package $package)
    {
        $request->validate([
            'partner' => ['required', new ExistsByHash(Partner::class)],
        ]);
        /** @var Partner $partner */
        $partner = Partner::byHash($request->partner);
        $inputs = array_merge($request->all(), [
            'showable' => [CodeLogable::SHOW_ADMIN, CodeLogable::SHOW_CUSTOMER]
        ]);

        if ($inputs['statusType'] === Code::TYPE_MANIFEST) {
            $inputs['status'] = $inputs['deliveryType'].'_'.$inputs['status'];
            $inputs['description'] = '[ADMIN]['.$partner->code.'] '.$inputs['description'];
        }
        $job = new CreateNewLog($package->code, $partner, $inputs);
        $this->dispatch($job);
        return (new Response(Response::RC_SUCCESS))->json();
    }


    public function orderAssignation(Package $package, Partner $partner): JsonResponse
    {
        $job = new AssignFirstPartnerToPackage($package, $partner);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->package))->json();
    }
    public function paymentConfirm(Package $package)
    {
        // event(new PackagePaymentVerified($package));
        return (new Response(Response::RC_SUCCESS, $package->refresh()))->json();
    }

    public function cancel(Package $package)
    {
        event(new PackageCanceledByAdmin($package));
        return (new Response(Response::RC_SUCCESS, $package->refresh()))->json();
    }

    private function getPartners(Request $request, bool $hasTransporter = true): JsonResponse
    {
        $this->query = Partner::query();
        if ($hasTransporter) {
            $this->query = $this->query->whereHas('transporters', fn ($query) => $query->where('type', $request->transporter_type));
        }
        $this->query->search($request->q);

        return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
    }

    private function getTrackings(Package $package): JsonResponse
    {
        return (new Response(Response::RC_SUCCESS, $package->code->logs))->json();
    }
}
