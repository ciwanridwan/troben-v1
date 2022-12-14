<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlertTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:tree';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is alert for level tree of System Level Agreement';

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
        $levelTreeDelivery = $this->levelTreeDeliveries();
        $levelTreePackage = $this->levelTreePackages();

        $penaltyPackages = $this->setPenaltyPackages();
        $penaltyDeliveries = $this->setPenaltyDeliveries();

        Log::info('Alert Level Tree Has Been Seen To Partners', array($levelTreeDelivery, $levelTreePackage));
        Log::info('Partner will get a penalty if late doing task', array($penaltyDeliveries, $penaltyPackages));
        $this->info('Alert Level Tree Has Been Seen To Partners');
    }

    /**
     * Set alert level 3 for deliveries
     */
    public function levelTreeDeliveries()
    {
        $q = "UPDATE partner_delivery_performances t
        SET level  = 3,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND level = 2
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_delivery_performances
                WHERE 1=1
                AND level = 3
                AND status = 1
            )";

        $result = DB::statement($q);
        return $result;
    }

    /** Set alert level 3 for packages */
    public function levelTreePackages()
    {
        $q = "UPDATE partner_package_performances t
        SET level = 3,
            deadline = deadline + interval '24' hour,
            updated_at = NOW()
        WHERE 1=1
            AND level = 2
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_package_performances
                WHERE 1=1
                AND level = 3
                AND status = 1
                AND package_id = t.package_id
                AND partner_id  = t.partner_id
            )";

        $result = DB::statement($q);
        return $result;
    }

    /** Set penalty if partner get late */
    public function setPenaltyPackages()
    {
        $q = "UPDATE partner_package_performances t
        SET status = 10,
            updated_at = NOW()
        WHERE 1=1
            AND level = 3
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_package_performances
                WHERE 1=1
                AND level = 3
                AND status = 10
                AND reached_at is null
                and deadline < now()
            )";

        $result = DB::statement($q);
        return $result;
    }

    /** Set penalty of deliveries
     * when partner get late doing task
     */
    public function setPenaltyDeliveries()
    {
        $q = "UPDATE partner_delivery_performances t
        SET status = 10,
            updated_at = NOW()
        WHERE 1=1
            AND level = 3
            AND status = 1
            AND reached_at IS NULL
            AND deadline < NOW()
            and not exists (
                select 1
                from partner_delivery_performances
                WHERE 1=1
                AND level = 3
                AND status = 10
                AND reached_at is null
                and deadline < now()
            )";

        $result = DB::statement($q);
        return $result;
    }
}
