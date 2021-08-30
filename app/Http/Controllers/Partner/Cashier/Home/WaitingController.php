<?php

namespace App\Http\Controllers\Partner\Cashier\Home;

use App\Http\Response;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;

class WaitingController extends Controller
{
    use HasResource;
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
    protected array $rules = [
        'q' => ['nullable'],
    ];

    public function __construct()
    {
        $this->baseBuilder();
    }


    public function customer_view(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['items', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices']);
            $this->query->where('status', Package::STATUS_WAITING_FOR_APPROVAL);

            $this->query->whereHas('code', function ($query) use ($request) {
                $query->whereRaw("LOWER(content) like '%".strtolower($request->q)."%'");
            });

            $this->attributes = $request->validate($this->rules);
            $this->getResource();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }

    public function payment_view(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['items', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices']);
            $this->query->where('status', Package::PAYMENT_STATUS_PENDING);

            $this->query->whereHas('code', function ($query) use ($request) {
                $query->whereRaw("LOWER(content) like '%".strtolower($request->q)."%'");
            });

            $this->attributes = $request->validate($this->rules);
            $this->getResource();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }

    public function revision_view(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['items', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices']);
            $this->query->where('status', Package::STATUS_REVAMP);

            $this->query->whereHas('code', function ($query) use ($request) {
                $query->whereRaw("LOWER(content) like '%".strtolower($request->q)."%'");
            });

            $this->attributes = $request->validate($this->rules);
            $this->getResource();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }
}
