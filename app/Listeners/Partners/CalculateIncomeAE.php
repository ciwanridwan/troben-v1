<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Dooring\DriverDooringFinished;
use App\Models\Partners\AgentProfitAE;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CalculateIncomeAE
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Deliveries\Dooring\DriverDooringFinished  $event
     * @return void
     */
    public function handle(DriverDooringFinished $event)
    {
        $delivery = $event->delivery;
        $deliveryId = $delivery->getKey();

        $q = "SELECT v.id voucher_claim_id, a.id agent_id, c.id coordinator_id, p.amount
        FROM voucher_claimed_customers v
        LEFT JOIN vouchers mv ON v.voucher_id = mv.id
        LEFT JOIN ae_vouchers av ON mv.aevoucher_id = av.id
        LEFT JOIN agents a ON av.user_id = a.user_id
        LEFT JOIN agents c ON a.referral_code_inv = c.referral_code
        LEFT JOIN package_prices p ON v.package_id = p.package_id AND p.type = 'service' AND p.description = 'service'
        WHERE 1=1 AND
        av.id IS NOT NULL AND
        p.amount IS NOT NULL AND
        a.user_id IS NOT NULL AND
        v.package_id IN (
            SELECT id
            FROM packages
            WHERE id IN (
                SELECT deliverable_id
                FROM deliverables
                WHERE 1=1 AND
                delivery_id = %d AND
                is_onboard = false AND
                status = 'unload_by_destination_package' AND
                deliverable_type = 'App\Models\Packages\Package'
            )
        )";

        $q = sprintf($q, $deliveryId);
        collect(DB::select($q))->each(function($r) {
            $profitMitra = $r->amount * 0.3; // for mitra
            $profitHO = $r->amount * 0.7; // for ho
            $profitAgent = $profitMitra * 0.3; // for agent
            $profitCoordinator = $r->amount * 0.05; // for coordinator

            if (! is_null($r->agent_id)) {
                AgentProfitAE::updateOrCreate([
                    'user_id' => $r->agent_id,
                    'voucher_claim_id' => $r->voucher_claim_id,
                    'profit_type' => AgentProfitAE::TYPE_AGENT,
                ], [
                    'user_id' => $r->agent_id,
                    'voucher_claim_id' => $r->voucher_claim_id,
                    'profit_type' => AgentProfitAE::TYPE_AGENT,
                    'commission' => $profitAgent,
                ]);
            }
            if (! is_null($r->coordinator_id)) {
                AgentProfitAE::updateOrCreate([
                    'user_id' => $r->agent_id,
                    'voucher_claim_id' => $r->voucher_claim_id,
                    'profit_type' => AgentProfitAE::TYPE_COORDINATOR,
                ], [
                    'user_id' => $r->agent_id,
                    'voucher_claim_id' => $r->voucher_claim_id,
                    'profit_type' => AgentProfitAE::TYPE_COORDINATOR,
                    'commission' => $profitCoordinator,
                ]);
            }
        });
    }
}
