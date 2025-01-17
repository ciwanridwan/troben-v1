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
use App\Jobs\Partners\Transporter\UpdateExistingTransporterByOwner;
use App\Jobs\Users\Actions\VerifyExistingUser;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

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
     *
     */
    // protected Builder $query;

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
            'driver' => ['nullable', 'boolean'], // nullable because mobile is running to hit endpoint and validation, actually should required
            'search' => ['nullable', 'string']
        ])->validate();

        $this->partner = $request->user()->partners->first()->fresh();

        $search = $this->attributes['search'] ?? null;

        return $this->attributes['type'] === 'transporter'
            ? $this->getTransporter($search)
            : $this->getEmployees($this->attributes['driver'] ?? null, $search);
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

        // old script for response after create
         return $type === 'transporter'
             ? $this->getTransporter(null)
             : $this->getEmployee();
        //return $type === 'transporter' ? (new Response(Response::RC_CREATED, ['message' => 'Armada berhasil ditambah']))->json() : (new Response(Response::RC_CREATED, ['message' => 'Pegawai berhasil ditambah']))->json();
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
    public function getEmployees($driver, $search): JsonResponse
    {
        $query = User::query();
        $queryPartnerId = fn ($builder) => $builder->where('partners.id', $this->partner->id);
        $query->with(['partners' => $queryPartnerId, 'transporters']);
        $query->whereHas('partners', $queryPartnerId);

        if ($driver === '1' && !is_null($driver)) {
            $query->whereHas('partners', function ($q) {
                $q->where('userables.role', '=', 'driver');
            });
        } else {
            $query->whereHas('partners', function ($q) {
                $q->where('userables.role', '!=', 'owner');
            });
        }

        if ($search !== "''") {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%');
                $q->orWhere('username', 'ilike', '%' . $search . '%');
                $q->orWhere('email', 'ilike', '%' . $search . '%');
                $q->orWhere('phone', 'ilike', '%' . $search . '%');
            });
        }

        $query->orderBy('name');
        $query->groupBy('id');

        return $this->jsonSuccess(UserResource::collection($query->paginate(request()->input('per_page', 10))));
    }
    public function getEmployee(): JsonResponse
    {
        return (new Response(Response::RC_SUCCESS, $this->employee))->json();
    }

    public function getTransporter($search): JsonResponse
    {
        $query = Transporter::query();
        $queryPartnerId = fn ($builder) => $builder->where('id', $this->partner->id);
        $query->with(['partner' => $queryPartnerId]);
        $query->whereHas('partner', $queryPartnerId);
        $query->orderBy('created_at', 'desc');

        if ($search !== "''") {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'ilike', '%' . $search . '%');
                $q->orWhere('registration_number', 'ilike', '%' . $search . '%');
                $q->orWhere('registration_year', 'ilike', '%' . $search . '%');
            });
        }

        return $this->jsonSuccess(TransporterResource::collection($query->paginate(request()->input('per_page', 10))));
    }

    public function deleteEmployee($hash): JsonResponse
    {
        $user = User::byHashOrFail($hash);


        $job = new DeleteExistingUser($user);
        $this->dispatch($job);
        $this->employee = $job->user;

        // return $this->getEmployee();
        return (new Response(Response::RC_SUCCESS, ['message' => 'Data pegawai berhasil dihapus']))->json();
    }

    public function deleteTransporter($hash): JsonResponse
    {
        $transporter = Transporter::byHashOrFail($hash);
        $job = new  DeleteExistingTransporter($transporter);
        $this->dispatch($job);

        // return $this->getTransporter();
        return (new Response(Response::RC_SUCCESS, ['message' => 'Data armada berhasil dihapus']))->json();
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

        $newRoles = "INSERT INTO role_users_v2 (user_id, role_id, created_at, updated_at)
            SELECT
                user_id,
                CASE
                    WHEN role = 'customer-service' THEN 'trawlpack-partner-cs'
                    ELSE CONCAT('trawlpack-partner-', role)
                END role_id,
                NOW(),
                NOW()
            FROM userables
            WHERE userable_type = 'App\Models\Partners\Partner' AND user_id = %d
            ON CONFLICT (user_id, role_id)
            DO UPDATE SET updated_at = NOW()";

        $newRoles = sprintf($newRoles, $job->user->id);
        DB::statement($newRoles);

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
            // update roles v1
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

            // update roles v2
            $deleteExistRoles = "DELETE FROM role_users_v2 WHERE user_id = %d";
            $deleteExistRoles = sprintf($deleteExistRoles, $job->user->id);
            DB::statement($deleteExistRoles);

            $updateRolesV2 = "INSERT INTO role_users_v2 (user_id, role_id, created_at, updated_at)
            SELECT
                user_id,
                CASE
                    WHEN role = 'customer-service' THEN 'trawlpack-partner-cs'
                    ELSE CONCAT('trawlpack-partner-', role)
                END role_id,
                NOW(),
                NOW()
            FROM userables
            WHERE userable_type = 'App\Models\Partners\Partner' AND user_id = %d
            ON CONFLICT (user_id, role_id)
            DO UPDATE SET updated_at = NOW()";

            $updateRolesV2 = sprintf($updateRolesV2, $job->user->id);
            DB::statement($updateRolesV2);
        }
        $user = $job->user;
        if ($user->type == UserablePivot::ROLE_DRIVER) {
            $user->load('transporters');
        }

        return (new Response(Response::RC_SUCCESS, $user))->json();
    }

    protected function updateTransporter(Request $request, $hash): JsonResponse
    {
        $transporter = Transporter::byHash($hash);
        if (is_null($transporter)) {
            return (new Response(Response::RC_BAD_REQUEST, ['Message' => 'Hash Invalid, Please Send Correct Hash']))->json();
        }

        $job = new UpdateExistingTransporterByOwner($transporter, $request->all());
        $this->dispatch($job);
        return (new Response(Response::RC_SUCCESS, ['Message' => "Data Armada Berhasil Diperbaharui"]))->json();
    }
}
