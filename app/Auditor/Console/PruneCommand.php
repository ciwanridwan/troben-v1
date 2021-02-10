<?php

namespace App\Auditor\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class PruneCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auditor:prune {--days=} {--force?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune audit trails and retains for any given day(s).';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return false;
        }

        $retains = $this->option('days') ?: config('auditor.log_lifetime');
        $now = Carbon::now();

        if ($this->hasOption('force') === false) {
            $count = app('trawlbens.auditor')->query()->whereDate('created_at', '<=', $now->subDays($retains))->count();
            $this->info("A total number of audits that will be prune is: $count. Dating back as far as ".$now->format('F j, Y'));
        }

        $confirmed = $this->confirm('Are you sure?');

        if ($confirmed or $this->hasOption('force')) {
            app('trawlbens.auditor')->query()->whereDate('created_at', '<=', $now->subDays($retains))->delete();
            $this->info('Audit logs has been pruned!');
        }
    }
}
