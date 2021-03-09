<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;

class PaymentController extends Controller
{
    use HasResource;

    /**
     * Filtered attributes.
     * @var array
     */
    protected array $attributes;

    /**
     * @var Builder
     */
    protected Builder $query;

    protected string $model = Package::class;

    /**
     * Request rule definitions.
     * @var array
     */
    protected array $rules = [
        'q' => ['nullable'],
    ];

    public function __construct()
    {
        $this->baseBuilder();
    }


    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.master.payment.index');
    }
}
