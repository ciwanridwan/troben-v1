<?php

namespace App\Console\Commands;

use App\Models\Partners\AgentProfitAE;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IncomeSalesGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tb:income';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $q = "SELECT p.id package_id, pp.amount, a.id agent_id, ac.id coordinator_id
        FROM deliverables d
        LEFT JOIN packages p ON d.deliverable_id = p.id
        LEFT JOIN package_prices pp ON p.id = pp.package_id AND pp.type = 'service' AND pp.description = 'service'
        LEFT JOIN customers c ON p.customer_id = c.id
        LEFT JOIN agents a ON c.referral_code = a.referral_code
        LEFT JOIN agents ac ON a.referral_code_inv = ac.referral_code
        LEFT JOIN (SELECT DISTINCT package_id FROM ae_agent_profit) pr ON p.id = pr.package_id
        WHERE d.deliverable_type = 'App\Models\Packages\Package'
        AND d.status = 'unload_by_destination_package'
        AND d.deliverable_id IN (
            SELECT packages.id
            FROM packages
            WHERE customer_id IN (
                SELECT c.id customer_id
                from customers c
                LEFT JOIN agents a ON c.referral_code = a.referral_code
                WHERE a.id IS NOT NULL
            )
        )
        AND pr.package_id IS NULL
        AND d.created_at >= now() - INTERVAL '30 DAYS'";

        $result = collect(DB::select($q))->each(function ($r) {
            $profitMitra = $r->amount * 0.3; // for mitra
            $profitHO = $r->amount * 0.7; // for ho
            $profitAgent = (int) ($profitMitra * 0.3); // for agent
            $profitCoordinator = (int) ($r->amount * 0.05); // for coordinator

            if (! is_null($r->agent_id)) {
                $agent = DB::table('agents')->where('id', $r->agent_id)->first();
                AgentProfitAE::updateOrCreate([
                    'user_id' => $agent->user_id,
                    'voucher_claim_id' => null,
                    'profit_type' => AgentProfitAE::TYPE_AGENT,
                    'package_id' => $r->package_id,
                ], [
                    'user_id' => $agent->user_id,
                    'voucher_claim_id' => null,
                    'profit_type' => AgentProfitAE::TYPE_AGENT,
                    'commission' => $profitAgent,
                    'package_id' => $r->package_id,
                ]);
            }
            if (! is_null($r->coordinator_id)) {
                $agent = DB::table('agents')->where('id', $r->coordinator_id)->first();
                AgentProfitAE::updateOrCreate([
                    'user_id' => $agent->user_id,
                    'voucher_claim_id' => null,
                    'profit_type' => AgentProfitAE::TYPE_COORDINATOR,
                    'package_id' => $r->package_id,
                ], [
                    'user_id' => $agent->user_id,
                    'voucher_claim_id' => null,
                    'profit_type' => AgentProfitAE::TYPE_COORDINATOR,
                    'commission' => $profitCoordinator,
                    'package_id' => $r->package_id,
                ]);
            }
        });

        $this->info('hit!');
        return 0;
    }
}
