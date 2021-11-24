<?php

namespace App\Http\Controllers\Admin\Master\Withdraw;

use App\Concerns\Controllers\HasResource;
use App\Events\Partners\Balance\WithdrawalSuccess;
use App\Http\Response;
use App\Models\Payments\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PendingController extends Controller
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
            $this->query->where('status', Withdrawal::STATUS_CREATED);
            $this->query->with(['partner'])->has('partner');

            if ($request->q != null) {
                $this->getSearch($request);
            }

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('admin.master.payment.withdraw.pending.index');
    }


    public function success(Withdrawal $withdrawal)
    {
        $withdrawal->status = Withdrawal::STATUS_SUCCESS;
        $withdrawal->save();

        event(new WithdrawalSuccess($withdrawal));

        return (new Response(Response::RC_SUCCESS))->json();
    }


    public function rejection(Withdrawal $withdrawal)
    {
        $withdrawal->status = Withdrawal::STATUS_REJECTED;
        $withdrawal->save();


        return (new Response(Response::RC_SUCCESS))->json();
    }
}
