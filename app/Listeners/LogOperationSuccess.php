<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class LogOperationSuccess {

    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @throws \JsonException
     */
    public function handle(JobProcessed $event): void {
        $job = $event->job;

        DB::table('success_jobs')->insert([
            'job_id' => $job->getJobId(),
            'queue' => $job->getQueue(),
            'connection' => $job->getConnectionName(),
            'payload' => json_encode($job->payload(), JSON_THROW_ON_ERROR),
            'result' => json_encode($job->result, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
