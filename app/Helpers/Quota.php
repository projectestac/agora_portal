<?php

namespace App\Helpers;

use App\Models\Instance;
use App\Models\Service;
use Illuminate\Http\Request;

class Quota {

    public static function getQuota(int $instanceId): array {
        if (empty($instanceId)) {
            return [];
        }

        $instance = Instance::where('id', $instanceId)->first();

        return [
            'quota' => $instance->quota,
            'used_quota' => $instance->used_quota,
        ];
    }

    public static function addToQuota(Request $request, int $size = 0) {
        $currentClient = Cache::getCurrentClient($request);
        $currentInstance = Instance::where('client_id', $currentClient['id'])
            ->where('service_id', Service::select('id')->where('name', 'Moodle')->get()->toArray())
            ->where('status', 'active')
            ->first()
            ->toArray();

        $instance = Instance::where('id', $currentInstance['id'])->first();
        $instance->used_quota += $size;

        return $instance->save();
    }

    public static function subtractFromQuota(Request $request, int $size = 0) {
        $currentClient = Cache::getCurrentClient($request);
        $currentInstance = Instance::where('client_id', $currentClient['id'])
            ->where('service_id', Service::select('id')->where('name', 'Moodle')->get()->toArray())
            ->where('status', 'active')
            ->first()
            ->toArray();

        $instance = Instance::where('id', $currentInstance['id'])->first();
        $instance->used_quota -= $size;

        return $instance->save();
    }

    public static function getDiskUsage(string $dir = '') {
        if (empty($dir)) {
            return [];
        }

        $result = exec('du -sb ' . $dir);
        preg_match("/^[0-9]*/", $result, $usedQuota);

        // Return the first element of the array.
        return reset($usedQuota);
    }

    /**
     * Check if the instance can request more quota.
     *
     * @param Instance $instance
     * @return bool
     */
    public static function canRequestQuota(Instance $instance): bool {

        $quotaPercentLimit = (float)Util::getConfigParam('quota_usage_to_request'); // 0.75
        $quotaFreeLimit = (float)Util::getConfigParam('quota_free_to_request'); // 3

        $quotaPercentUsed = round($instance->used_quota / $instance->quota, 4);
        $quotaRemaining = round(($instance->quota - $instance->used_quota) / (1024 * 1024 * 1024), 4);

        return ($quotaPercentUsed > $quotaPercentLimit) && ($quotaRemaining < $quotaFreeLimit);

    }

}
