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

        $partner = $request->user()->partners()->first();

        if ($partner->type === 'transporter') {
            $currentBalance = $this->getActualBalanceTransporter($request);
        } else {
            $currentBalance = $this->getActualBalance($request);
        }

        return [
            // 'current_balance' => $deposit - $withdraw,
            'current_balance' => $currentBalance,
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

    public function getActualBalanceTransporter($request)
    {
        $partner = $request->user()->partners()->first();
        $query = "SELECT
        coalesce(SUM(amount), 0)
    FROM
        (
            SELECT
                *
            FROM
                (
                    SELECT
                        content receipt_income,
                        pbh.amount,
                        pbh.created_at
                    FROM
                        (
                            SELECT
                                package_id,
                                SUM(
                                    CASE
                                        WHEN partner_balance_histories.type NOT IN ('penalty', 'discount', 'withdraw') THEN partner_balance_histories.balance
                                        ELSE partner_balance_histories.balance * -1
                                    END
                                ) amount,
                                MAX(partner_balance_histories.created_at) created_at
                            FROM
                                partner_balance_histories
                                LEFT JOIN partners ON partner_balance_histories.partner_id = partners.id
                            WHERE
                                partners.code = '$partner->code'
                                AND package_id IS NOT NULL -- ignore penalty
                                AND partner_balance_histories.type != 'penalty'
                                AND partner_balance_histories.description != 'penalty'
                            GROUP BY
                                package_id
                        ) pbh
                        LEFT JOIN codes c ON pbh.package_id = c.codeable_id
                        AND c.codeable_type = 'App\Models\Packages\Package'
                    UNION
                    ALL
                    SELECT
                        c.content receipt_income,
                        pbdh.balance amount,
                        pbdh.created_at
                    FROM
                        partner_balance_delivery_histories pbdh
                        LEFT JOIN codes c ON pbdh.delivery_id = c.codeable_id
                        AND c.codeable_type = 'App\Models\Deliveries\Delivery'
                        LEFT JOIN partners ON pbdh.partner_id = partners.id
                    WHERE
                        partners.code = '$partner->code'
                        AND pbdh.type = 'deposit'
                ) income
                LEFT JOIN LATERAL (
                    SELECT
                        receipt receipt_disbursed
                    FROM
                        disbursment_histories
                        LEFT JOIN partner_balance_disbursement ON disbursment_histories.disbursment_id = partner_balance_disbursement.id
                        LEFT JOIN partners ON partner_balance_disbursement.partner_id = partners.id
                    WHERE
                        partners.code = '$partner->code'
                ) disbursed ON income.receipt_income = disbursed.receipt_disbursed
            WHERE
                1 = 1 -- AND income.created_at >= '2023-08-03 13:29:18'
                AND receipt_disbursed IS NULL
            ORDER BY
                income.created_at DESC
        ) ssss";

        $result = collect(DB::select($query))->first();
        Partner::query()->where('code', $partner->code)->update(['balance' => $result->balance]);
        
        return (int)$result->balance;
    }
}
