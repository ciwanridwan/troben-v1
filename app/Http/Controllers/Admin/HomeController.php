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
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
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
                'destination_sub_district', 'deliveries', 'deliveries.partner', 'code', 'attachments', 'motoBikes','canceled',
                'multiDestination', 'parentDestination',
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

            $result = $this->query->paginate(request('per_page', 15));

            $itemCollection = $result->getCollection()->map(function ($r) {
                $shipping_method = 'Standart';
                $order_mode = false;
                // todo if status is paid return true
                if ($r->multiDestination->count()) {
                    $order_mode = true;
                }
                if (! is_null($r->parentDestination)) {
                    $order_mode = false;
                }

                if ($r->service_code == Service::TRAWLPACK_EXPRESS) {
                    $shipping_method = 'Express';
                }
                if ($r->service_code == Service::TRAWLPACK_CUBIC) {
                    $shipping_method = 'Cubic';
                }

                $r->order_mode = $order_mode ? 'Single' : 'Multiple';
                $r->shipping_method = $shipping_method;

                unset($r->multiDestination);
                unset($r->parentDestination);

                return $r;
            })->values();

            $result->setCollection($itemCollection);

            return (new Response(Response::RC_SUCCESS, $result))->json();
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

    public function loginother(Request $request)
    {
        $users = [];

        $search = $request->get('search');
        if ($search) {
            $q = "SELECT
                u.id,
                MAX(u.name) name,
                MAX(username) username,
                MAX(email) email,
                STRING_AGG(p.code, ',') partner,
                MAX(u.deleted_at) deleted_at,
                STRING_AGG(uu.role, ', ') roles
            FROM users u
            LEFT JOIN userables uu ON u.id = uu.user_id AND uu.userable_type = 'App\Models\Partners\Partner'
            LEFT JOIN partners p ON uu.userable_id = p.id
            WHERE u.email ILIKE '%".$search."%' OR u.username ILIKE '%".$search."%' OR u.name ILIKE '%".$search."%' OR p.code ILIKE '%".$search."%'
            GROUP BY u.id";
            $users = DB::select($q);
        }

        return view('admin.superadmin.loginother', compact('users'));
    }

    public function loginotherSubmit(Request $request)
    {
        $rule = ['email' => 'required'];
        $this->validate($request, $rule);

        $user = User::where('email', $request->get('email'))->firstOrFail();

        Auth::guard('web')->logout();
        Auth::guard('web')->loginUsingId($user->getKey());

        return redirect('/');
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
