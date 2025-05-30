<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HttpAccessLog;
use Carbon\Carbon;

class PurgeOldHttpAccessLogs extends Command
{
    protected $signature = 'logs:purge-old';

    protected $description = 'Suprimeix els registres HTTP més antics de 3 mesos';

    public function handle()
    {
        $monthsToKeep = 12;
        $cutoffDate = Carbon::now()->subMonths($monthsToKeep);

        $deletedCount = HttpAccessLog::where('accessed_at', '<', $cutoffDate)->delete();

        $this->info("Purgat completat: $deletedCount registres eliminats.");
    }
}
