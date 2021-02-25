<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Employees\Employee;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use App\Http\Resources\Admin\Master\EmployeeResource;
use App\Http\Resources\Admin\Master\PartnerResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Employees\DeleteExistingEmployee;
use App\Http\Resources\Admin\MasterEmployeeResource;
use App\Jobs\Users\DeleteExistingUser;
use App\Jobs\Users\UpdateExistingUser;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

use function PHPSTORM_META\type;

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
            ['type']
        ]
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
                'resource' => EmployeeResource::collection($this->query->paginate(request('per_page', 15)))
            ];
            $data = array_merge($data, $this->extraData());

            return (new Response(Response::RC_SUCCESS, $data));
        }

        return view('admin.master.employee.index');
    }

    public function update(Request $request)
    {
        $userable = (new $this->model)->byHashOrFail($request->hash);
        $job = new UpdateExistingUser($userable->user, $request->all());
        $this->dispatch($job);
        return (new Response(Response::RC_SUCCESS, $job->user))->json();
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
            'roles' => UserablePivot::ROLES
        ];
        return $data;
    }
}
