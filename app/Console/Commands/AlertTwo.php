<?php

namespace App\Console\Commands;

use App\Models\Partners\Performances\Package;
use App\Models\Partners\Performances\PerformanceModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlertTwo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:two';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is alert level two of System Level Aggreement';

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

        $levelTwo = Package::query()->where('level', 1)->where('deadline', '<', $now)->where('status', PerformanceModel::STATUS_ON_PROCESS)->update([
            'level' => 2,
            'deadline' => Carbon::now()->endOfDay()
        ]);

        Log::info('Alert Level Two Has Been Seen To Partners', array($levelTwo));
        $this->info('Alert Level Two Has Been Seen To Partners');
    }
}
