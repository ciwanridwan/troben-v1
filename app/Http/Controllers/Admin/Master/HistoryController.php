<?php

namespace App\Http\Controllers\Admin\Master;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class HistoryController extends Controller
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
    protected array $rules = [
        'q' => ['nullable'],
    ];

    function __construct()
    {
        $this->baseBuilder();
    }

    public function paid(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            $this->getResource();
            $this->query = $this->query->paid();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))));
        }
        return view('admin.master.history.paid.index');
    }

    public function cancel(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            $this->getResource();
            $this->query = $this->query->failed();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))));
        }
        return view('admin.master.history.cancel.index');
    }
}
