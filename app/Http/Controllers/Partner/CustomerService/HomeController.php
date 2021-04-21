<?php

namespace App\Http\Controllers\Partner\CustomerService;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
use App\Supports\Repositories\PartnerRepository;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;

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
            $this->query = $partnerRepository->queries()->getDeliveriesQuery()->whereHas('packages')->with(['packages', 'packages.code']);

            $this->attributes = $request->validate($this->rules);

            if ($request->has('transporter')) {
                $this->query = $partnerRepository->getPartner()->transporters()->getQuery();
                $this->query = $this->query->where('type', $request->type)
                    ->with('users', function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->q . '%');
                    });
                $this->getResource();
                // dd($this->query->get()->toArray(), $this->query->toSql());
            } else {
                $this->getResource();
            }

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.customer-service.home.index');
    }

    public function orderAssignation(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $job = new AssignDriverToDelivery($delivery, $userablePivot);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
    }
}
