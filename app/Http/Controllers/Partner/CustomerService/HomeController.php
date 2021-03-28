<?php

namespace App\Http\Controllers\Partner\CustomerService;

use App\Http\Response;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Partners\Transporter;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use App\Supports\Repositories\PartnerRepository;

class HomeController extends Controller
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


    public function index(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            $this->query = $partnerRepository->queries()->getDeliveriesQuery()->getQuery();

            $this->attributes = $request->validate($this->rules);

            if ($request->has('transporter')) {
                $this->query = $partnerRepository->getPartner()->transporters()->getQuery();
                $this->getResource();
                $this->query = $this->query->whereHas('drivers', function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%'.$request->q.'%');
                        $query->orWhere('transporters.registration_name', 'LIKE', '%'.$request->q.'%');
                    });
                })->with('drivers');
            } else {
                $this->getResource();
            }

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.customer-service.home.index');
    }

    public function orderAssignation(Delivery $delivery, Transporter $transporter): JsonResponse
    {
        /** @var \App\Models\User $driver */
        $driver = $transporter->drivers->first();

        $job = new AssignDriverToDelivery($delivery, $driver->pivot);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
    }
}
