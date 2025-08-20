<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOperation;
use App\Models\Instance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class QueueController extends Controller {

    const PER_PAGE = 25;

    /**
     * @throws \JsonException
     */
    public function getPending(): View {
        $jobs = DB::table('jobs')->paginate(self::PER_PAGE);
        $data = [];

        foreach ($jobs as $job) {
            $payLoad = json_decode($job->payload, false, 512, JSON_THROW_ON_ERROR);
            $operationData = unserialize($payLoad->data->command, ['allowed_classes' => [ProcessOperation::class]]);

            $instance = Instance::join('clients', 'clients.id', '=', 'instances.client_id')
                ->join('services', 'services.id', '=', 'instances.service_id')
                ->where('clients.dns', $operationData->data['instance_dns'])
                ->where('services.name', $operationData->data['service_name'])
                ->first();

            $data[] = [
                'id' => $job->id,
                'queue' => $job->queue,
                'operation_data' => $operationData->data,
                'instance' => $instance,
                'attempts' => $job->attempts,
                'created_at' => Carbon::parse($job->created_at)->format('d/m/Y H:i'),
            ];
        }

        return view('admin.batch.queue-pending')
            ->with('data', $data)
            ->with('links', $jobs->links('pagination::bootstrap-4'));
    }

    /**
     * @throws \JsonException
     */
    public function getSuccess(Request $request): View {
        $query = $request->input('q');

        $jobs = DB::table('success_jobs')
            ->when($query, function($q) use ($query) {
                $q->where(function($q2) use ($query) {
                    $q2->where('payload', 'like', "%{$query}%")
                    ->orWhere('payload', 'like', "%{$query}%") // ou autres colonnes si besoin
                    ->orWhere('result', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        $data = [];
        foreach ($jobs as $job) {
            $payLoad = json_decode($job->payload, false, 512, JSON_THROW_ON_ERROR);
            $operationData = unserialize($payLoad->data->command, ['allowed_classes' => [ProcessOperation::class]]);
            $result = json_decode($job->result, false, 512, JSON_THROW_ON_ERROR);

            $instance = Instance::join('clients', 'clients.id', '=', 'instances.client_id')
                ->join('services', 'services.id', '=', 'instances.service_id')
                ->where('clients.dns', $operationData->data['instance_dns'])
                ->where('services.name', $operationData->data['service_name'])
                ->first();

            $data[] = [
                'id' => $job->id,
                'queue' => $job->queue,
                'operation_data' => $operationData->data,
                'instance' => $instance,
                'result' => $result,
                'queued_at' => Carbon::parse($job->queued_at)->format('d/m/Y H:i'),
                'created_at' => Carbon::parse($job->created_at)->format('d/m/Y H:i'),
                'updated_at' => Carbon::parse($job->updated_at)->format('d/m/Y H:i'),
            ];
        }

        return view('admin.batch.queue-success')
            ->with('data', $data)
            ->with('links', $jobs->appends(['q' => $query])->links('pagination::bootstrap-4'));
    }

    public function getFail(Request $request): View {
        $query = $request->input('q');

        $jobs = DB::table('failed_jobs')
            ->when($query, function($q) use ($query) {
                $q->where(function($q2) use ($query) {
                    $q2->where('payload', 'like', "%{$query}%")
                    ->orWhere('exception', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        $data = [];
        foreach ($jobs as $job) {
            $payLoad = json_decode($job->payload, false, 512, JSON_THROW_ON_ERROR);
            $operationData = unserialize($payLoad->data->command, ['allowed_classes' => [ProcessOperation::class]]);
            $exception = $job->exception;

            $instance = Instance::join('clients', 'clients.id', '=', 'instances.client_id')
                ->join('services', 'services.id', '=', 'instances.service_id')
                ->where('clients.dns', $operationData->data['instance_dns'])
                ->where('services.name', $operationData->data['service_name'])
                ->first();

            $data[] = [
                'id' => $job->id,
                'queue' => $job->queue,
                'operation_data' => $operationData->data,
                'instance' => $instance,
                'exception' => $exception,
                'failed_at' => Carbon::parse($job->failed_at)->format('d/m/Y H:i'),
            ];
        }

        return view('admin.batch.queue-fail')
            ->with('data', $data)
            ->with('links', $jobs->appends(['q' => $query])->links('pagination::bootstrap-4'));
    }

    public function destroy(int $jobId): RedirectResponse {

        $result = DB::table('jobs')->where('id', $jobId)->delete();

        if ($result) {
            return redirect()->back()->with('success', __('batch.queue_operation_removed'));
        }

        return redirect()->back()->with('error', __('batch.queue_operation_not_removed'));

    }

    public function execute(int $id): RedirectResponse {

        $job = DB::table('jobs')->find($id);

        if (!$job) {
            return redirect()->back()->with('error', __('batch.queue_operation_not_found'));
        }

        try {

            $payLoad = json_decode($job->payload, false, 512, JSON_THROW_ON_ERROR);
            $operationData = unserialize($payLoad->data->command, ['allowed_classes' => [ProcessOperation::class]]);

            // Execute the operation synchronously.
            ProcessOperation::dispatchSync((array)$operationData->data);

            // Remove the job from the queue.
            DB::table('jobs')->where('id', $id)->delete();
            return redirect()->back()->with('success', __('batch.queue_operation_executed'));

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', __('batch.queue_execution_failed'));
        }
    }

}
