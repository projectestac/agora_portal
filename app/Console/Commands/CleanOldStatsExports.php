<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

// Cron job to run this command:
// * * * * * cd /dades/html/portal && php artisan schedule:run >> storage/logs/scheduler.log 2>&1

// To see logs:
// tail -f storage/logs/scheduler.log

class CleanOldStatsExports extends Command
{
    protected $signature = 'clean:stats-exports';
    protected $description = 'Esborra els fitxers stats_* de més d\'una setmana al directori public/exports';

    public function handle()
    {
        $directory = public_path('exports');
        $now = Carbon::now();
        $deletedCount = 0;
        $expirationDays = 7;

        \Log::info("Netejant fitxers antics a {$directory}...");

        if (!File::exists($directory)) {
            \Log::info("La carpeta {$directory} no existeix.");
            return;
        }

        $files = File::files($directory);

        foreach ($files as $file) {
            // Only process files that start with 'stats_'
            if (str_starts_with($file->getFilename(), 'stats_')) {
                $lastModified = Carbon::createFromTimestamp($file->getMTime());

                if ($now->diffInDays($lastModified) > $expirationDays) {
                    File::delete($file->getPathname());
                    \Log::info("Esborrat: {$file->getFilename()}");
                    $deletedCount++;
                }
            }
        }

        \Log::info("Neteja completada. {$deletedCount} fitxer(s) esborrat(s).");
    }
}
?>
