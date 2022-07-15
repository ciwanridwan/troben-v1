<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Model;

class AgentProfitAE extends Model
{
    public const TYPE_AGENT = 'agent';
    public const TYPE_COORDINATOR = 'coordinator';

    protected $table = 'ae_agent_profit';

    protected $fillable = [
        'user_id',
        'voucher_claim_id',
        'profit_type',
        'commission',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
