<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;

class PaymentController extends Controller
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
    protected string $delivery = Delivery::class;

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

    public function getSearchPackage(Request $request)
    {
        $this->query = $this->query->search($request->q);
        $this->query->orWhereHas('code', function ($query) use ($request) {
            $query->search($request->q);
        });
        return $this;
    }

    public function getSearchDelivery(Request $request)
    {
        $this->query->whereHas('packages');
        $this->query->whereHas('code', function ($query) use ($request) {
            $query->search($request->q, 'content');
        });
        $this->query->whereHas('partner', function ($query) use ($request) {
            $query->search($request->partner_code, 'code');
        });
        return $this;
    }

    public function dataRelation()
    {
        $this->query->with(['histories.partner', 'items', 'items.prices', 'origin_regency', 'origin_district', 'origin_sub_district', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'attachments']);
        return $this;
    }

    public function paginateWithTransformData()
    {
        $paginator = $this->query->orderBy('created_at', 'desc')->paginate(request('per_page', 1));
        $paginator->getCollection()->transform(function ($item) {
            $item->packages->each(fn ($package) => $package->items->each(fn ($package_item) => $item->weight_borne_total += $package_item->weight_borne));
            foreach($item->packages as $package){
                if (isset($package->historyTransporter)){
                    foreach ($package->historyTransporter as $transport){
                        $package->transporter_funds = $transport->balance;
                    }
                }
            }
            return $item;
        });


        return $paginator;
    }

    public function home(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query->with(['histories', 'historyBusiness.partner', 'items', 'items.prices', 'origin_regency', 'origin_district', 'origin_sub_district', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'attachments']);
            if ($request->q != null){
                $this->getSearchPackage($request);
            }
            if ($request->partner_code != null){
                $this->query->whereHas('historyBusiness', function ($query) use ($request) {
                    $query->whereHas('partner', function ($q) use ($request) {
                        $q->search($request->partner_code, 'code');
                    });
                });
            }
            $this->query->has('historyBusiness');
            $this->query->has('histories');
            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.master.payment.home');
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query->with(['histories', 'historyBusiness.partner', 'items', 'items.prices', 'origin_regency', 'origin_district', 'origin_sub_district', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'attachments']);
            if ($request->q != null){
                $this->getSearchPackage($request);
            }
            if ($request->partner_code != null){
                $this->query->whereHas('historyBusiness', function ($query) use ($request) {
                    $query->whereHas('partner', function ($q) use ($request) {
                        $q->search($request->partner_code, 'code');
                    });
                });
            }
            $this->query->has('historyBusiness');
            $this->query->has('histories');

            // PartnerBalanceReportRepository get data sum penghasilan

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.master.payment.partner.business');
    }

    public function getIncomeMTAK(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query = $this->delivery::query();
            if ($request->q != null ){
                $this->getSearchDelivery($request);
            }
            if ($request->partner_code != null){
                $this->query->whereHas('packages.historyTransporter', function ($query) use ($request) {
                    $query->whereHas('partner', function ($q) use ($request) {
                        $q->search($request->partner_code, 'code');
                    });
                });
            }
            $this->query->where('type', Delivery::TYPE_TRANSIT);
            $this->query->where('status', Delivery::STATUS_FINISHED);
            $this->query->whereNotNull('userable_id');
            $this->query->with(['code', 'driver.partners', 'partner', 'origin_partner', 'packages.code','packages.histories', 'packages.historyTransporter.partner']);

            $this->query->has('packages.historyTransporter.partner');
            $this->query->has('packages.histories');

            return (new Response(Response::RC_SUCCESS, $this->paginateWithTransformData()));
        }

        return view('admin.master.payment.partner.transporter');
    }

    public function getIncomeMTAKab(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query = $this->delivery::query();
            if ($request->q != null ){
                $this->getSearchDelivery($request);
            }
            if ($request->partner_code != null){
                $this->query->whereHas('packages.historyTransporter', function ($query) use ($request) {
                    $query->whereHas('partner', function ($q) use ($request) {
                        $q->search($request->partner_code, 'code');
                    });
                });
            }
            $this->query->where('type', Delivery::TYPE_TRANSIT);
            $this->query->where('status', Delivery::STATUS_FINISHED);
            $this->query->whereNotNull('userable_id');
            $this->query->with(['code', 'driver.partners', 'partner', 'origin_partner', 'packages.code','packages.histories', 'packages.historyTransporter.partner']);
            $this->query->whereHas('partner', function (Builder $query) {
                $query->where('code', 'MB-DJB-0001');
                $query->orWhere('code', 'MTM-MKS-00001');
                $query->orWhere('code', 'MT-UPG-0007');
                $query->orWhere('code', 'MTM-SBY-00000');
            });
            $this->query->has('packages.historyTransporter.partner');
            $this->query->has('packages.histories');

            return (new Response(Response::RC_SUCCESS, $this->paginateWithTransformData()));
        }

        return view('admin.master.payment.partner.transporter');
    }

    public function getIncomeMPW(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query->with(['historyPool.partner', 'items', 'items.prices', 'origin_regency', 'origin_district', 'origin_sub_district', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'attachments']);
            if ($request->q != null){
                $this->getSearchPackage($request);
            }
            if ($request->partner_code != null){
                $this->query->whereHas('historyPool', function ($query) use ($request) {
                    $query->whereHas('partner', function ($q) use ($request) {
                        $q->search($request->partner_code, 'code');
                    });
                });
            }
            $this->query->has('historyPool');


            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.master.payment.partner.pool');
    }

    public function getIncomeSpace(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query->with(['historySpace.partner', 'items', 'items.prices', 'origin_regency', 'origin_district', 'origin_sub_district', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'attachments']);

            if ($request->q != null){
                $this->getSearchPackage($request);
            }
            if ($request->partner_code != null){
                $this->query->whereHas('historySpace', function ($query) use ($request) {
                    $query->whereHas('partner', function ($q) use ($request) {
                        $q->search($request->partner_code, 'code');
                    });
                });
            }
            $this->query->has('historySpace');


            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.master.payment.partner.space');
    }
}
