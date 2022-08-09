<?php

namespace App\Http\Controllers\Admin\Master\Withdraw;

use App\Concerns\Controllers\HasResource;
use App\Events\Partners\Balance\WithdrawalConfirmed;
use App\Events\Partners\Balance\WithdrawalRejected;
use App\Http\Response;
use App\Models\Partners\Partner;
use App\Models\Payments\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RequestController extends Controller
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
        // if ($request->expectsJson()) {
        //     $this->query->where('status', Withdrawal::STATUS_REQUESTED);
        //     $this->query->with(['partner']);
        //     $this->query->has('partner');

        //     if ($request->q != null) {
        //         $this->getSearch($request);
        //     }
        //     return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        // }

        return view('admin.master.payment.withdraw.request.index');
    }

    public function detail()
    {
        return view('admin.master.payment.withdraw.request.index.detail');
    }


    // public function confirmation(Withdrawal $withdrawal)
    // {
    //     $partner = Partner::where($withdrawal->partner_id)->first();
    //     $partner->balance = $partner->balance - $withdrawal->amount;
    //     $partner->save();

    //     $withdrawal->status = Withdrawal::STATUS_CONFIRMED;
    //     $withdrawal->last_balance = $partner->balance;
    //     $withdrawal->save();

    //     event(new WithdrawalConfirmed($withdrawal));

    //     return (new Response(Response::RC_SUCCESS))->json();
    // }


    // public function rejection(Withdrawal $withdrawal)
    // {
    //     $withdrawal->status = Withdrawal::STATUS_REJECTED;
    //     $withdrawal->last_balance = $withdrawal->first_balance;
    //     $withdrawal->save();

    //     event(new WithdrawalRejected($withdrawal));

    //     return (new Response(Response::RC_SUCCESS))->json();
    // }
}
