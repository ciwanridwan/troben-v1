<?php

namespace App\Console\Commands;

use App\Models\Partners\Performances\Delivery;
use Illuminate\Console\Command;
use App\Models\Partners\Performances\Package;
use App\Models\Partners\Performances\PerformanceModel;
use Carbon\Carbon;
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
        $now = Carbon::now();

        $levelTree = Delivery::query()->where('level', 2)->where('deadline', '<', $now)->where('status', PerformanceModel::STATUS_ON_PROCESS)->update([
            'level' => 3,
            'deadline' => Carbon::now()->endOfDay()
        ]);

        Log::info('Alert Level Tree Has Been Seen To Partners', array($levelTree));
        $this->info('Alert Level Tree Has Been Seen To Partners');
    }
}
