<?php

namespace App\Http\Controllers\Admin;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;

class HomeController extends Controller
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
    protected string $model = Package::class;

    /**
     * @var array
     */
    protected array $rules;

    public function __construct()
    {
        $this->rules = [
            'q' => ['nullable'],
        ];

        $this->baseBuilder();
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query->with('items');
            $this->query->whereDoesntHave('deliveries');

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.home.index');
    }

    public function orderAssignation(Package $package, Partner $partner): JsonResponse
    {
        $job = new AssignFirstPartnerToPackage($package, $partner);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->package))->json();
    }
}
