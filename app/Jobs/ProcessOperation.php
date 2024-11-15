<?php

namespace App\Jobs;

use App\Http\Controllers\OperationController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessOperation implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    public array $result = [];
    public bool $success = false;

    public int $timeout = 1800; // Half an hour. This variable is for laravel internals.
    public const TIMEOUT = 1800; // Half an hour. This variable is used in the handle() function.

    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Execute the job. To simulate a failed job, the function handle() must throw an exception.
     */
    public function handle(): bool {

        $action = $this->data['action'];
        $serviceName = $this->data['service_name'];
        $params = $this->data['params'];
        $dns = $this->data['instance_dns'];

        $file = OperationController::get_operations_exec_file($serviceName);

        set_time_limit(0);
        ini_set('mysql.connect_timeout', self::TIMEOUT);
        ini_set('default_socket_timeout', self::TIMEOUT);

        $paramsCommand = ' -s="' . $action . '" --ccentre="' . $dns . '"';

        if (is_array($params) && count($params) > 0) {
            foreach ($params as $key => $value) {
                // The fields 'password' and 'xtecadminPassword' contain characters $ which
                // are considered as variables in bash. To avoid this, we use single quotes.
                if (str_contains($key, 'assword')) {
                    $paramsCommand .= ' --' . $key . '=\'' . $value . '\'';
                } else {
                    $value = str_replace(["\r", "\n"], '<br/>', $value);
                    $paramsCommand .= ' --' . $key . '="' . html_entity_decode($value) . '"';
                }
            }
        }

        $command = 'nohup php ' . $file . $paramsCommand . ' > /dev/stdout 2>&1';

        $last = exec($command, $result);
        $this->job->result = $result;

        $queued_at = DB::table('jobs')->where('id', $this->job->getJobId())->value('available_at');
        $this->job->queued_at = $queued_at;

        $success = $last === 'success';
        $this->success = $success;

        return $success;

    }

}
