<?php

namespace App\Http\Controllers\Api\Partner;

use App\Models\User;
use App\Http\Response;
use App\Exceptions\Error;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use App\Jobs\Users\CreateNewUser;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Partners\Transporter;
use App\Jobs\Users\DeleteExistingUser;
use App\Jobs\Users\UpdateExistingUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Resources\Api\Partner\Asset\UserResource;
use App\Jobs\Partners\Transporter\CreateNewTransporter;
use App\Jobs\Partners\Transporter\AttachDriverToTransporter;
use App\Jobs\Partners\Transporter\DeleteExistingTransporter;
use App\Http\Resources\Api\Partner\Asset\TransporterResource;
use App\Jobs\Users\Actions\VerifyExistingUser;

class AssetController extends Controller
{
    use DispatchesJobs;

    /**
     * Partner instances.
     *
     * @var \App\Models\Partners\Partner
     */
    protected Partner $partner;

    /**
     * @var User
     */
    protected User $employee;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Get partner assets
     * Route Path       : {API_DOMAIN}/partner/asset
     * Route Name       : api.partner.asset
     * Route Method     : GET.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => ['required', Rule::in(['transporter', 'employee'])],
            'driver' => ['nullable', 'boolean'],
        ])->validate();

        $this->partner = $request->user()->partners->first()->fresh();

        return $this->attributes['type'] === 'transporter'
            ? $this->getTransporter()
            : $this->getEmployees($this->attributes['driver']);
    }

    /**
     * Storing new partner's asset.
     *
     * Route Path       : {API_DOMAIN}/partner/asset/{type}
     * Route Name       : api.partner.asset.store
     * Route Method     : POST.
     *
     * @param \Illuminate\Http\Request  $request
     * @param mixed                     $type
     *
     * @return JsonResponse
     */
    public function store(Request $request, $type): JsonResponse
    {
        $this->partner = $request->user()->partners->first();

        $type === 'transporter' ? $this->createTransporter($request) : $this->createEmployee($request);

        return $type === 'transporter'
            ? $this->getTransporter()
            : $this->getEmployee();
    }

    /**
     * Deleting partner's asset.
     *
     * Route Path       : {API_DOMAIN}/partner/asset/{type}/{hash}
     * Route Name       : api.partner.asset.destroy
     * Route Method     : DELETE.
     *
     * @param \Illuminate\Http\Request  $request
     * @param mixed                     $type
     * @param mixed                     $hash
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, $type, $hash): JsonResponse
    {
        $this->partner = $request->user()->partners->first();

        return $type === 'transporter' ? $this->deleteTransporter($hash) : $this->deleteEmployee($hash);
    }

    /**
     * Deleting partner's asset.
     *
     * Route Path       : {API_DOMAIN}/partner/asset/{type}/{hash}
     * Route Name       : api.partner.asset.update
     * Route Method     : PATCH.
     *
     * @param \Illuminate\Http\Request  $request
     * @param mixed                     $type
     * @param mixed                     $hash
     *
     * @return JsonResponse
     */
    public function update(Request $request, $type, $hash): JsonResponse
    {
        $this->partner = $request->user()->partners->first();

        return $type === 'transporter' ? $this->updateTransporter($request, $hash) : $this->updateEmployee($request, $hash);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function fusion(Request $request): JsonResponse
    {
        $job = new AttachDriverToTransporter($request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }
    public function getEmployees($driver): JsonResponse
    {
        if ($driver === '1') {
            $employees = $this->partner->users()->wherePivotIn('role', ['driver'])->orderBy('name')->get()->groupBy('id');
        } else {
            $employees = $this->partner->users()->wherePivotNotIn('role', ['owner'])->orderBy('name')->get()->groupBy('id');
        }

        return $this->jsonSuccess(new UserResource(collect($employees)));
    }
    public function getEmployee(): JsonResponse
    {
        return (new Response(Response::RC_SUCCESS, $this->employee))->json();
    }

    public function getTransporter(): JsonResponse
    {
        return $this->jsonSuccess(new TransporterResource(collect($this->partner->transporters->fresh())));
    }

    public function deleteEmployee($hash): JsonResponse
    {
        $user = User::byHashOrFail($hash);


        $job = new DeleteExistingUser($user);
        $this->dispatch($job);
        $this->employee = $job->user;

        return $this->getEmployee();
    }

    public function deleteTransporter($hash): JsonResponse
    {
        $transporter = Transporter::byHashOrFail($hash);
        $job = new  DeleteExistingTransporter($transporter);
        $this->dispatch($job);

        return $this->getTransporter();
    }

    /**
     * Creating new employee from partner.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     * @throws \Throwable
     */
    protected function createEmployee(Request $request): void
    {
        $job = new CreateNewUser($request->all());
        $this->dispatch($job);

        throw_if(!$job, Error::make(Response::RC_DATABASE_ERROR));

        $verifyJob = new VerifyExistingUser($job->user);
        $this->dispatch($verifyJob);

        foreach ($request->role as $role) {
            $pivot = new UserablePivot();
            $pivot->fill([
                'user_id' => $job->user->id,
                'userable_type' => Model::getActualClassNameForMorph(get_class($this->partner)),
                'userable_id' => $this->partner->getKey(),
                'role' => $role,
            ])->save();
        }
        $this->employee = $job->user;
    }

    /**
     * Creating new Transporter from partner.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    protected function createTransporter(Request $request): void
    {
        $job = new CreateNewTransporter($this->partner, $request->all());
        $this->dispatch($job);

        throw_if(!$job, Error::make(Response::RC_DATABASE_ERROR));
    }

    protected function updateEmployee(Request $request, $hash): JsonResponse
    {
        $user = User::byHashOrFail($hash);
        $job = new UpdateExistingUser($user, $request->all());
        $this->dispatch($job);

        if ($request->role) {
            foreach ($request->role as $role) {
                UserablePivot::firstOrCreate([
                    'user_id' => $job->user->id,
                    'userable_type' => Model::getActualClassNameForMorph(get_class($this->partner)),
                    'userable_id' => $this->partner->getKey(),
                    'role' => $role,
                ]);
            }
            UserablePivot::whereNotIn('role', $request->role)
                ->where('user_id', $job->user->id)
                ->delete();
        }
        $user = $job->user;
        if ($user->type == UserablePivot::ROLE_DRIVER) {
            $user->load('transporters');
        }

        return (new Response(Response::RC_SUCCESS, $user))->json();
    }

    protected function updateTransporter(Request $request, $hash): JsonResponse
    {
        # CODE UPDATE
    }
}
