<?php

namespace App\Console\Commands;

use App\Actions\Core\SlaLevel;
use Illuminate\Console\Command;

class SlaSetterFunc extends Command
{
    protected $signature = 'core:sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is worker for level SLA';

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
        SlaLevel::doSlaSetter();

        $this->info('SLA Level Has Been Seen To Partners');
    }
}
