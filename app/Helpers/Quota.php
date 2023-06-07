<?php

namespace App\Helpers;

use App\Models\Instance;
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
        $currentInstanceId = Cache::getCurrentInstance($request)['id'];
        $instance = Instance::where('id', $currentInstanceId)->first();
        $instance->used_quota += $size;

        return $instance->save();
    }

    public static function subtractFromQuota(Request $request, int $size = 0) {
        $currentInstanceId = Cache::getCurrentInstance($request)['id'];
        $instance = Instance::where('id', $currentInstanceId)->first();
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

}
