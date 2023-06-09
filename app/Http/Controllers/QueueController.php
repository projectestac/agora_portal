<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOperation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class QueueController extends Controller {
    /**
     * @throws \JsonException
     */
    public function getPending(): View {
        $jobs = DB::table('jobs')->paginate(10);
        $data = [];

        foreach ($jobs as $job) {
            $payLoad = json_decode($job->payload, false, 512, JSON_THROW_ON_ERROR);
            $operationData = unserialize($payLoad->data->command, ['allowed_classes' => [ProcessOperation::class]]);

            $data[] = [
                'id' => $job->id,
                'queue' => $job->queue,
                'operationData' => $operationData->data,
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
    public function getSuccess(): View {
        $jobs = DB::table('success_jobs')->orderByDesc('id')->paginate(10);
        $data = [];

        foreach ($jobs as $job) {
            $payLoad = json_decode($job->payload, false, 512, JSON_THROW_ON_ERROR);
            $operationData = unserialize($payLoad->data->command, ['allowed_classes' => [ProcessOperation::class]]);
            $result = json_decode($job->result, false, 512, JSON_THROW_ON_ERROR);

            $data[] = [
                'id' => $job->id,
                'queue' => $job->queue,
                'operationData' => $operationData->data,
                'result' => $result,
                'queued_at' => Carbon::parse($job->queued_at)->format('d/m/Y H:i'),
                'created_at' => Carbon::parse($job->created_at)->format('d/m/Y H:i'),
                'updated_at' => Carbon::parse($job->updated_at)->format('d/m/Y H:i'),
            ];
        }

        return view('admin.batch.queue-success')
            ->with('data', $data)
            ->with('links', $jobs->links('pagination::bootstrap-4'));
    }

    public function getFail(): View {
        $jobs = DB::table('failed_jobs')->orderByDesc('id')->paginate(10);
        $data = [];

        foreach ($jobs as $job) {
            $payLoad = json_decode($job->payload, false, 512, JSON_THROW_ON_ERROR);
            $operationData = unserialize($payLoad->data->command, ['allowed_classes' => [ProcessOperation::class]]);
            $exception = $job->exception;

            $data[] = [
                'id' => $job->id,
                'queue' => $job->queue,
                'operationData' => $operationData->data,
                'exception' => $exception,
                'failed_at' => Carbon::parse($job->failed_at)->format('d/m/Y H:i'),
            ];
        }

        return view('admin.batch.queue-fail')
            ->with('data', $data)
            ->with('links', $jobs->links('pagination::bootstrap-4'));
    }

    public function destroy(int $jobId): RedirectResponse {

        $result = DB::table('jobs')->where('id', $jobId)->delete();

        if ($result) {
            return redirect()->back()->with('success', __('batch.queue_operation_removed'));
        }

        return redirect()->back()->with('error', __('batch.queue_operation_not_removed'));

    }

}
