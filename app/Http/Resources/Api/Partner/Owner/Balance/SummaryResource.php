<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use App\Models\Partners\Balance\History;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class SummaryResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $deposit = $this->getTotalBalance(History::TYPE_DEPOSIT);
        $withdraw = $this->getTotalBalance(History::TYPE_WITHDRAW);
        $deposit_today = $this->getTotalBalance(History::TYPE_DEPOSIT, true);
        $withdraw_today = $this->getTotalBalance(History::TYPE_WITHDRAW, true);

        $user = $request->user()->partners()->first();
        $balance = intval($user->balance);

        return [
            // 'current_balance' => $deposit - $withdraw,
            'current_balance' => $balance,
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
        return ! $isToday ? $this->where('type', $type)->sum('balance')
            : $this
                ->where('type', $type)
                ->where('created_at', '>', Carbon::today())
                ->where('created_at', '<', Carbon::tomorrow())
                ->sum('balance');
    }
}
