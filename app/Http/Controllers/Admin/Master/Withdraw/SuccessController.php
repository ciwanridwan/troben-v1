<?php

namespace App\Http\Controllers\Admin\Master\Withdraw;

use App\Concerns\Controllers\HasResource;
use App\Http\Response;
use App\Models\Payments\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SuccessController extends Controller
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
    protected string $model = Withdrawal::class;

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

    public function getSearch(Request $request)
    {
        $this->query->whereHas('partner', function ($query) use ($request) {
            $query->search($request->q, 'code');
        });
        return $this;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $this->query->where('status', Withdrawal::STATUS_SUCCESS);
            $this->query->orWhere('status', Withdrawal::STATUS_CANCELLED);
            $this->query->with(['partner'])->has('partner');

            if ($request->q != null){
                $this->getSearch($request);
            }
            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.master.payment.withdraw.success.index');
    }
}
