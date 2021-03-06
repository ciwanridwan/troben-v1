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
use App\Http\Resources\Api\Partner\asset\UserResource;
use App\Jobs\Partners\Transporter\CreateNewTransporter;
use App\Jobs\Partners\Transporter\DeleteExistingTransporter;
use App\Http\Resources\Api\Partner\Asset\TransporterResource;

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
     * @return void
     */
    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => ['required',Rule::in(['transporter','employee'])],
        ])->validate();

        $this->partner = $request->user()->partners->first()->fresh();

        return $this->attributes['type'] == 'transporter'
                ? $this->getTransporter()
                : $this->getEmployee();
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

        $type == 'transporter' ? $this->createTransporter($request) : $this->createEmployee($request);

        return $type == 'transporter'
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

        return $type == 'transporter' ? $this->deleteTransporter($hash) : $this->deleteEmployee($hash);
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

        return $type == 'transporter' ? $this->updateTransporter($request, $hash) : $this->updateEmployee($request, $hash);
    }

    public function getEmployee(): JsonResponse
    {
        return $this->jsonSuccess(new UserResource(collect($this->partner->users()->wherePivotNotIn('role', ['owner'])->get()->groupBy('id'))));
    }

    public function getTransporter(): JsonResponse
    {
        return $this->jsonSuccess(new TransporterResource(collect($this->partner->transporters->fresh())));
    }

    public function deleteEmployee($hash)
    {
        $user = (new User())->byHashOrFail($hash);
        $job = new DeleteExistingUser($user);
        $this->dispatch($job);

        return $this->getEmployee();
    }

    public function deleteTransporter($hash)
    {
        $transporter = (new Transporter())->byHashOrFail($hash);
        $job = new  DeleteExistingTransporter($transporter);
        $this->dispatch($job);

        return $this->getTransporter();
    }

    /**
     * Creating new employee from partner.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function createEmployee(Request $request): void
    {
        $job = new CreateNewUser($request->all());
        $this->dispatch($job);

        throw_if(! $job, Error::make(Response::RC_DATABASE_ERROR));

        foreach ($request->role as $role) {
            $pivot = new UserablePivot();
            $pivot->fill([
                'user_id' => $job->user->id,
                'userable_type' => Model::getActualClassNameForMorph(get_class($this->partner)),
                'userable_id' => $this->partner->getKey(),
                'role' => $role,
            ])->save();
        }
    }

    /**
     * Creating new Transporter from partner.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function createTransporter(Request $request): void
    {
        $job = new CreateNewTransporter($this->partner, $request->all());
        $this->dispatch($job);

        throw_if(! $job, Error::make(Response::RC_DATABASE_ERROR));
    }

    protected function updateEmployee(Request $request, $hash): JsonResponse
    {
        $user = (new User())->byHashOrFail($hash);
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

        return $this->getEmployee();
    }

    protected function updateTransporter(Request $request, $hash)
    {
        # CODE UPDATE
    }
}
