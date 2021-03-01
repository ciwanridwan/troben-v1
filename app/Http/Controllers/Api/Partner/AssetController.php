<?php

namespace App\Http\Controllers\Api\Partner;

use App\Exceptions\Error;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\Partner\asset\UserResource;
use App\Http\Resources\Api\Partner\Asset\TransporterResource;
use App\Http\Response;
use App\Jobs\Users\CreateNewUser;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

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

        return $request->type == 'transporter'
                ? $this->getTransporter()
                : $this->getEmployee();
    }

    /**
     * Storing new partner's asset.
     *
     * Route Path       : {API_DOMAIN}/partner/asset
     * Route Name       : api.partner.asset.store
     * Route Method     : POST.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request, $type)
    {
        $this->partner = $request->user()->partners->first();

        $type == 'transporter' ? $this->createTransporter($request) : $this->createEmployee($request);

        return $type == 'transporter'
                ? $this->getTransporter()
                : $this->getEmployee();
    }

    public function getEmployee(): JsonResponse
    {
        return $this->jsonSuccess(new UserResource(collect($this->partner->users()->wherePivotNotIn('role', ['owner'])->get()->groupBy('id'))));
    }

    public function getTransporter(): JsonResponse
    {
        return $this->jsonSuccess(new TransporterResource(collect($this->partner->transporters->fresh())));
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
        $this->attributes = Validator::make($request->all(),[
            'name' => ['required'],
            'username' => ['required','unique:users,username'],
            'email' => ['required','unique:users,email'],
            'phone' => ['required','unique:users,phone','numeric','phone:AUTO,ID'],
            'password' => ['required'],
            'role' => ['required'],
        ])->validate();

        $job = new CreateNewUser($this->attributes);
        $this->dispatch($job);

        throw_if(!$job,Error::make(Response::RC_DATABASE_ERROR));

        foreach ($this->attributes['role'] as $role) {
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
        // DO CREATE TRANSPORTER
    }
}
