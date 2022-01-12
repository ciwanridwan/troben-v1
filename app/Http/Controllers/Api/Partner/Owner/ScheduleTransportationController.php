<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\ScheduleTransportationResource;
use App\Http\Response;
use App\Jobs\Partners\SchedulesTransportation\CreateNewSchedules;
use App\Jobs\Partners\SchedulesTransportation\DeleteExistingSchedules;
use App\Jobs\Partners\SchedulesTransportation\UpdateExistingSchedules;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleTransportationController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected $attributes;
    /**
     * Get Type of Promo List
     * Route Path       : {API_DOMAIN}/partner/owner/schedule
     * Route Name       : api.v.office
     */

    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'q' => 'nullable',
            'id' => 'nullable',
        ])->validate();

        $query = $this->getBasicBuilder(ScheduleTransportation::query());

        return $this->jsonSuccess(ScheduleTransportationResource::collection($query->paginate(request('per_page', 15))));
    }

    public function store(Request $request)
    {
        $partner_id = $request->user()->partners->first()->id;
        if ($partner_id){
            $request['partner_id'] = $partner_id;
            $request['origin_regency_id'] = $request['origin_regency'];
            $request['destination_regency_id'] = $request['destination_regency'];
            $request['departed_at'] = date('Y-m-d',strtotime($request['departure_at']));
            unset($request['departure_at'], $request['destination_regency'], $request['origin_regency']);
        }else{
            return (new Response(Response::RC_UNAUTHORIZED))->json();
        }
        $this->attributes = $request->all();
        $job = new CreateNewSchedules($this->attributes);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS))->json();
    }


    public function destroy(Request $request)
    {
        $schedules = ScheduleTransportation::find($request->id);
        if ($schedules == null) return (new Response(Response::RC_DATA_NOT_FOUND))->json();

        $job = new DeleteExistingSchedules($schedules);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS))->json();
    }

    public function update(Request $request)
    {
        $partner_id = $request->user()->partners->first()->id;
        $schedules = ScheduleTransportation::find($request->id);
        if ($schedules == null) return (new Response(Response::RC_DATA_NOT_FOUND))->json();

        $request['partner_id'] = $partner_id;
        if ($request->has('origin_regency')) {
            $request['origin_regency_id'] = $request['origin_regency'];
        }
        if ($request->has('destination_regency')) {
            $request['destination_regency_id'] = $request['destination_regency'];
        }
        if ($request->has('departure_at')) {
            $request['departed_at'] = date('Y-m-d',strtotime($request['departure_at']));
        }
        unset($request['departure_at'], $request['destination_regency'], $request['origin_regency']);

        $job = new UpdateExistingSchedules($schedules, $request->all());
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS))->json();
    }

    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(
            request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('name', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }
}
