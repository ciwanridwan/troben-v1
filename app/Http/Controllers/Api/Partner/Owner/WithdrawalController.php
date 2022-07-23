<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Concerns\Controllers\HasResource;
use App\Events\Partners\Balance\WithdrawalRequested;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\UserBankResource;
use App\Http\Resources\Api\Partner\Owner\WithdrawalResource;
use App\Http\Response;
use App\Jobs\Partners\CreateNewBalanceDisbursement;
use App\Models\Partners\BankAccount;
use App\Models\Payments\Bank;
use App\Models\Payments\Withdrawal;
use App\Supports\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
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

    public function index(Request $request) : JsonResponse
    {
        $account = $request->user();
        $this->query->where('partner_id', $account->partners[0]->id);
        $this->query->with(['partner'])->has('partner');
        $this->query->when($request->input('status'), fn (Builder $builder, $input) => $builder->where('status', $input));
        if ($request->to == null) {
            $request->to = Carbon::now();
        }
        $this->query->when(request()->has('from'), fn ($q) => $q->whereBetween('created_at', [$request->from, $request->to]));

        if ($request->q != null) {
            $this->getSearch($request);
        }
        return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
    }

    public function store(Request $request, PartnerRepository $repository) : JsonResponse
    {
        if ($repository->getPartner()->balance < $request->amount) {
            return (new Response(Response::RC_INSUFFICIENT_BALANCE))->json();
        }
        // $request['status'] = Withdrawal::STATUS_CREATED;
        $request['status'] = Withdrawal::STATUS_REQUESTED;
        $job = new CreateNewBalanceDisbursement($repository->getPartner(), $request->all());
        $this->dispatch($job);
        
        event(new WithdrawalRequested($job->withdrawal));

        return $this->jsonSuccess(new WithdrawalResource($job->withdrawal));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccountBank(Request $request): JsonResponse
    {
        $account = $request->user();
        $query = BankAccount::query();
        $query->where('user_id', $account->id);

        return $this->jsonSuccess(UserBankResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * @return JsonResponse
     */
    public function getBank(): JsonResponse
    {
        $bank  = Bank::where('is_active', true)->get();

        return (new Response(Response::RC_SUCCESS, $bank))->json();
    }
}
