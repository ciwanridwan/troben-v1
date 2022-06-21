<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\ScheduleTransportationResource;
use App\Http\Resources\Api\GenericResource;
use App\Http\Resources\Api\Partner\Owner\ScheduleHarborDestResource;
use App\Http\Resources\Api\Partner\Owner\ScheduleHarborOriginResource;
use App\Http\Response;
use App\Jobs\Partners\SchedulesTransportation\CreateNewSchedules;
use App\Jobs\Partners\SchedulesTransportation\DeleteExistingSchedules;
use App\Jobs\Partners\SchedulesTransportation\UpdateExistingSchedules;
use App\Models\Partners\Harbor;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Route Name       : api.v.office.
     */
    public function index(Request $request): JsonResponse
    {
        $rule = [
            'q' => 'nullable',
        ];
        Validator::make($request->all(), $rule)->validate();

        $query = ScheduleTransportation::query()->with('harbor');

        if ($request->has('q')) {
            $query = $query->where('name', 'like', '%'.$request->get('q').'%');
        }

        $query = $query->paginate(request('per_page', 15));

        $result = ScheduleTransportationResource::collection($query);

        return $this->jsonSuccess($result);
    }

    public function listOrigin(Request $request): JsonResponse
    {
        $q = "SELECT origin_regency_id id, MAX(h.origin_name) harbor_name,  MAX(r.name) origin_name
        FROM harbors h
        LEFT JOIN geo_regencies r ON h.origin_regency_id = r.id
        WHERE deleted_at IS NULL
        GROUP BY origin_regency_id";

        $result = DB::select($q);

        $result = ScheduleHarborOriginResource::collection($result);

        return $this->jsonSuccess($result);
    }

    public function listDest(Request $request): JsonResponse
    {
        $req = $request->all();
        $rule = [
            'origin_id' => 'required',
        ];
        Validator::make($req, $rule)->validate();

        $q = "SELECT destination_regency_id id, MAX(h.destination_name) harbor_name,  MAX(r.name) destination_name
        FROM harbors h
        LEFT JOIN geo_regencies r ON h.destination_regency_id = r.id
        WHERE deleted_at IS NULL AND origin_regency_id = %d
        GROUP BY destination_regency_id";
        $q = sprintf($q, $req['origin_id']);

        $result = DB::select($q);

        $result = ScheduleHarborDestResource::collection($result);

        return $this->jsonSuccess($result);
    }

    public function store(Request $request)
    {
        $req = $request->all();
        $rule = [
            'ship_name' => 'required',
            'origin_regency_id' => 'required',
            'destination_regency_id' => 'required',
            'departed_at' => 'required|date_format:Y-m-d',
        ];
        Validator::make($req, $rule)->validate();

        $partner = $request->user()->partners->first();
        if (is_null($partner)) {
            return (new Response(Response::RC_UNAUTHORIZED))->json();
        }

        $harbor = Harbor::query()
            ->where('origin_regency_id', $req['origin_regency_id'])
            ->where('destination_regency_id', $req['destination_regency_id'])
            ->firstOrFail();

        $req['partner_id'] = $partner->id;
        $req['harbor_id'] = $harbor->id;

        $job = new CreateNewSchedules($req);
        $this->dispatch($job);
        $result = [$job];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }


    public function destroy(Request $request)
    {
        $req = $request->all();
        $rule = [
            'id' => 'required',
        ];
        Validator::make($req, $rule)->validate();

        $schedules = ScheduleTransportation::findOrFail($req['id']);

        $job = new DeleteExistingSchedules($schedules);
        $this->dispatch($job);

        return response()->json([
            'code' => '0000',
            'error' => null,
            'message' => 'Data Has Been Deleted',
            'data' => null
        ]);
        // return (new Response(Response::RC_SUCCESS))->json();
    }

    public function update(Request $request)
    {
        $req = $request->all();
        $rule = [
            'id' => 'required',
            'ship_name' => 'required',
            'origin_regency_id' => 'required',
            'destination_regency_id' => 'required',
            'departed_at' => 'required|date_format:Y-m-d',
        ];
        Validator::make($req, $rule)->validate();

        $partner = $request->user()->partners->first();
        if (is_null($partner)) {
            return (new Response(Response::RC_UNAUTHORIZED))->json();
        }

        $schedules = ScheduleTransportation::findOrFail($req['id']);

        $job = new UpdateExistingSchedules($schedules, $req);
        $this->dispatch($job);

        return response()->json([
            'code' => '0000',
            'error' => null,
            'message' => 'Update Data Has Been Successfully',
            'data' => null
        ]);
        // return (new Response(Response::RC_SUCCESS))->json();
    }

    public function shipSchedule()
    {
        $query = $this->getBasicBuilder(ScheduleTransportation::query());
        return $this->jsonSuccess(ScheduleTransportationResource::collection($query->paginate(request('per_page', 15))));
    }
}
