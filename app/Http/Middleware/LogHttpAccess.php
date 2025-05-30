<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\HttpAccessLog;

class LogHttpAccess
{
    public function handle(Request $request, Closure $next)
    {
        // For confidentiality, we do not log passwords or password confirmations.
        $input = $request->except(['_token', 'password', 'password_confirmation']);
        $payload = json_encode($input);
        $truncateLength = 100; // Maximum length for the payload
        $payload = strlen($payload) > $truncateLength ? substr($payload, 0, $truncateLength) . '...' : $payload;

        HttpAccessLog::create([
            'accessed_at' => now(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'payload' => $payload,
        ]);

        return $next($request);
    }
}
