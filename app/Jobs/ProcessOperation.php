<?php

namespace App\Jobs;

use App\Http\Controllers\OperationController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOperation implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    const TIMEOUT = 1800; // Half an hour.


    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle() {

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
                $value = str_replace("\r", '<br/>', $value);
                $value = str_replace("\n", '<br/>', $value);
                $paramsCommand .= ' --' . $key . '="' . html_entity_decode($value) . '"';
            }
        }

        $command = 'php ' . $file . $paramsCommand . ' > /dev/stdout 2>&1';

        $last = exec($command, $result);
        $success = $last === 'success';

        return true;

    }
}
