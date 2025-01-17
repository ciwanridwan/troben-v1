<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Employees\Employee;
use App\Http\Controllers\Controller;
use App\Jobs\Users\DeleteExistingUser;
use App\Jobs\Users\UpdateExistingUser;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Users\UpdateExistingUserRole;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Resources\Admin\Master\EmployeeResource;

class EmployeeController extends Controller
{
    use HasResource, DispatchesJobs;
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
    protected string $model = UserablePivot::class;

    /**
     * @var array
     */
    protected array $rules = [
        'role' => ['filled'],
        'q' => ['nullable'],
    ];

    protected array $byRelation = [
        'user' => [
            ['name'],
            ['email'],
            ['phone'],
        ],
        'userable' => [
            ['code'],
            ['type'],
        ],
    ];

    public function __construct()
    {
        $this->baseBuilder();
        $this->query = $this->query->with(['user', 'userable']);
    }

    /**
     *
     * Get All Employee Account
     * Route Path       : {API_DOMAIN}/account/Employee
     * Route Name       : api.account.Employee
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            $this->getResource();

            $data = [
                'resource' => EmployeeResource::collection($this->query->paginate(request('per_page', 15))),
            ];
            $data = array_merge($data, $this->extraData());

            return (new Response(Response::RC_SUCCESS, $data));
        }

        return view('admin.master.employee.index');
    }

    public function update(Request $request)
    {
        $userable = (new $this->model)->byHashOrFail($request->hash);
        $job_update_user_data = new UpdateExistingUser($userable->user, $request->all());
        $job_update_user_role = new UpdateExistingUserRole($userable, $request->all());
        $this->dispatch($job_update_user_data);
        $this->dispatch($job_update_user_role);

        return (new Response(Response::RC_SUCCESS, EmployeeResource::make($job_update_user_role->userable)))->json();
    }


    /**
     *
     *  Delete Employee
     * Route Path       : admin/master/Employee
     * Route Name       : admin.master.Employee
     * Route Method     : DELETE.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $userable = (new UserablePivot())->byHashOrFail($request->hash);
        $job = new DeleteExistingUser($userable->user);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->user))->json();
    }

    public function extraData()
    {
        $data = [
            'roles' => UserablePivot::getAvailableRoles(),
        ];

        return $data;
    }
}
