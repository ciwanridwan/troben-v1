<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

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

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }


        return view('admin.home.index');
    }
}
