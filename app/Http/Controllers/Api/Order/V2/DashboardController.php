<?php

namespace App\Http\Controllers\Api\Order\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Order\Dashboard\DetailResource;
use App\Http\Resources\Api\Order\Dashboard\ListDriverResource;
use App\Http\Resources\Api\Order\Dashboard\ListOrderResource;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Models\User;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use App\Services\Chatbox\Chatbox;

class DashboardController extends Controller
{
    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * Query to search
     */
    public function getSearch(Request $request)
    {
        $this->query = $this->query->where('type', $request->type)
            ->where(function (Builder $query) use ($request) {
                $query->search($request->q, 'registration_number');
                $query->orWhereHas('drivers', fn (Builder $userQuery) => $userQuery->search($request->q, 'name'));
            });
        return $this;
    }

    public function index(PartnerRepository $partnerRepository)
    {
        $this->query = $partnerRepository->queries()->getDeliveriesQuery()->whereHas('packages')->with([
            'packages', 'packages.code',
            'packages.origin_regency',
            'packages.origin_district',
            'packages.origin_sub_district',
            'packages.destination_regency',
            'packages.destination_district',
            'packages.destination_sub_district',
            'packages.prices',
            'packages.multiDestination',
            'packages.motoBikes'
        ]);

        $this->query->where('type', Delivery::TYPE_PICKUP)->where('status', Delivery::STATUS_PENDING);

        $this->query->orderBy('created_at', 'desc');

        return $this->jsonSuccess(ListOrderResource::collection($this->query->paginate(request('per_page', 15))));
    }

    /** Detail order trawlpack */
    public function detail(Package $package): JsonResponse
    {
        return $this->jsonSuccess(DetailResource::make($package));
    }

    /** Get List Driver */
    public function listDrivers(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        $this->query = $partnerRepository->getPartner()->transporters()->getQuery();
        $this->getSearch($request);

        $transporters = $this->query->get();
        $transporterCollections = $this->searchTransporter($transporters);

        return $this->jsonSuccess(ListDriverResource::collection($transporterCollections));
    }

    public function searchTransporter(Collection $transporters): Collection
    {
        $transporterDrivers = new Collection();

        $transporters->each(fn (Transporter $transporter) => $transporter->drivers->each(function (User $driver) use ($transporterDrivers, $transporter) {
            $transporterSelected = $transporter->only('type', 'registration_number');
            $driverSelected = $driver->only('name');
            $userableSelected = $driver->only('pivot');
            $hashUserable = ['hash' => $userableSelected['pivot']->hash];
            $driverArr = array_merge($driverSelected, $hashUserable);

            $transporter->unsetRelation('drivers');
            $transporterDriver = array_merge($transporterSelected, ['driver' => $driverArr]);
            $transporterDriver = new Collection($transporterDriver);
            $transporterDrivers->push($transporterDriver);
        }));
        return $transporterDrivers;
    }

    public function orderAssignation(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $method = 'partner';
        $job = new AssignDriverToDelivery($delivery, $userablePivot, $method);
        $this->dispatchNow($job);
        $driverSignIn = User::where('id', $job->delivery->assigned_to->user_id)->first();
        if ($driverSignIn) {
            $token = auth('api')->login($driverSignIn);
        }
        $param = [
            'token' => $token ?? null,
            'type' => 'trawlpack',
            'participant_id' => $job->delivery->assigned_to->user_id,
            'customer_id' => $delivery->packages[0]->customer_id,
            'package_id' => $job->delivery->packages[0]->id,
            'product' => 'trawlpack'
        ];

        try {
            Chatbox::createDriverChatbox($param);
        } catch (\Exception $e) {
            report($e);
        }
        return (new Response(Response::RC_SUCCESS, ['Message' => 'Driver berhasil di assign']))->json();
    }

    public function update()
    {

    }

    public function estimationPrices(Request $request)
    {

    }
}
