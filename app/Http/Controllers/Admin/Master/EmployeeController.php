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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Employees\DeleteExistingEmployee;
use App\Http\Resources\Admin\MasterEmployeeResource;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

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
    protected string $model = Partner::class;

    /**
     * @var array
     */
    protected array $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => ['filled'],
            'email' => ['filled'],
            'phone' => ['filled'],
            'q' => ['filled'],
        ];
        $this->baseBuilder();
        $this->query = $this->query->with('users');
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
        $this->resourceHandle($request);
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            foreach (Arr::except($this->attributes, 'q') as $key => $value) {
                $this->getByColumn($key);
            }
            if (Arr::has($this->attributes, 'q')) {
                $this->getSearch($this->attributes['q']);
            }




            return $this->jsonSuccess(EmployeeResource::collection($this->query->paginate(request('per_page', 15))));
        }

        return view('admin.master.employee.index');
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
        $Employee = (new Employee)->byHashOrFail($request->hash);
        $job = new DeleteExistingEmployee($Employee);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->Employee))->json();
    }

    public function resourceHandle(Request $request)
    {
        $users_partner = $this->query->get()->map(function ($item) {
            foreach ($item->users as $user) {
                $user->partner_type = $item->type;
                $user->partner_code = $item->code;
            }
            return $item;
        })->pluck('users');

        $employee = new Collection();
        foreach ($users_partner as $item) {
            $employee = $employee->merge($item);
        }
        dd((new User()->newCollection()));


        return EmployeeResource::collection($employee);
    }
}
