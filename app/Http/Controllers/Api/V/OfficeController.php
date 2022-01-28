<?php

namespace App\Http\Controllers\Api\V;

use App\Actions\Auth\AccountAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Http\Response;
use App\Jobs\Offices\CreateNewOfficer;
use App\Jobs\Offices\DeleteExistingOfficer;
use App\Jobs\Offices\UpdateExistingOfficer;
use App\Models\Offices\Office;
use App\Models\OneTimePassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected $attributes;
    /**
     * Get Type of Promo List
     * Route Path       : {API_DOMAIN}/v/sf/office
     * Route Name       : api.v.office.
     */
    public function authentication(Request $request): JsonResponse
    {
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['admin', 'user'])],
            'username' => ['required'],
            'password' => ['required'],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
            'device_name' => ['required'],
        ]);

        // override value
        $inputs['guard'] = 'office';
        $inputs['otp'] = $inputs['otp'] ?? false;

        return (new AccountAuthentication($inputs))->officeAttempt();
    }

    public function profile(Request $request)
    {
        $account = $request->auth;
        return $this->jsonSuccess(new OfficeResource($account));
    }

    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
//            'type' => 'required',
        ])->validate();

        $query = $this->getBasicBuilder(Office::query());

        return $this->jsonSuccess(OfficeResource::collection($query->paginate(request('per_page', 15))));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin')) {
            return (new Response(Response::RC_UNAUTHORIZED))->json();
        }

        $job = new CreateNewOfficer($request->all());
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS))->json();
    }


    public function destroy(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin')) {
            return (new Response(Response::RC_UNAUTHORIZED))->json();
        }
        $office = Office::find($request->id);
        if ($office == null) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }

        $job = new DeleteExistingOfficer($office);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS))->json();
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin')) {
            return (new Response(Response::RC_UNAUTHORIZED))->json();
        }
        $office = Office::find($request->id);
        if ($office == null) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }

        $job = new UpdateExistingOfficer($office, $request->all());
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
