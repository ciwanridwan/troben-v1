<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SummaryResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        // $deposit = $this->getTotalBalance(History::TYPE_DEPOSIT);
        // $withdraw = $this->getTotalBalance(History::TYPE_WITHDRAW);
        $deposit_today = $this->getTotalBalance(History::TYPE_DEPOSIT, true);
        $withdraw_today = $this->getTotalBalance(History::TYPE_WITHDRAW, true);

        $user = $request->user()->partners()->first();
        $balance = intval($user->balance);

        return [
            // 'current_balance' => $deposit - $withdraw,
            'current_balance' => $this->getActualBalance($request),
            'daily_income' => $deposit_today - $withdraw_today,
        ];
    }

    /**
     * Calculate balance.
     *
     * @param $type
     * @param bool $isToday
     * @return mixed
     */
    public function getTotalBalance($type, bool $isToday = false)
    {
        /** @var Collection|History[] $this */
        return !$isToday ? $this->where('type', $type)->sum('balance')
            : $this
            ->where('type', $type)
            ->where('created_at', '>', Carbon::today())
            ->where('created_at', '<', Carbon::tomorrow())
            ->sum('balance');
    }

    public function getActualBalance($request)
    {
        $partner = $request->user()->partners()->first();
        $query = "SELECT coalesce(SUM(amount), 0) as balance FROM
        (
        select
            *
        from
            (
            select
                content receipt_income,
                pbh.amount,
                pbh.created_at
            from
                (
                select
                    package_id,
                    SUM(
                    case
                        when partner_balance_histories.type not in ('penalty', 'discount', 'withdraw')
                        then partner_balance_histories.balance
                    else partner_balance_histories.balance * -1 end
                    ) amount,
                    MAX(partner_balance_histories.created_at) created_at
                from
                    partner_balance_histories
                left join partners on
                    partner_balance_histories.partner_id = partners.id
                where
                    partners.code = '$partner->code'
                    and package_id is not null
                group by
                    package_id
            ) pbh
            left join codes c on
                pbh.package_id = c.codeable_id
                and
                c.codeable_type = 'App\Models\Packages\Package'
        ) income
        left join lateral (
            select
                receipt receipt_disbursed
            from
                disbursment_histories
            left join partner_balance_disbursement on
                disbursment_histories.disbursment_id = partner_balance_disbursement.id
            left join partners on
                partner_balance_disbursement.partner_id = partners.id
            where
                partners.code = '$partner->code'
        ) disbursed on
            income.receipt_income = disbursed.receipt_disbursed
        where
            1 = 1
            and receipt_disbursed is null
        order by
            income.created_at desc

        ) ssss";

        $result = collect(DB::select($query))->first();
        Partner::query()->where('code', $partner->code)->update(['balance' => $result->balance]);
        
        return (int)$result->balance;
    }
}
