<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\HttpAccessLog;

class LogHttpAccess
{
    // Maximum length for the payload.
    public const TRUNCATE_LENGTH = 4000;

    /**
     * @throws \JsonException
     */
    public function handle(Request $request, Closure $next)
    {
        // For confidentiality, do not log passwords or password confirmations.
        $input = $request->except(['_token', 'password', 'password_confirmation']);
        $payload = json_encode($input, JSON_THROW_ON_ERROR);
        $payload = strlen($payload) > $this::TRUNCATE_LENGTH ? substr($payload, 0, $this::TRUNCATE_LENGTH) . '...' : $payload;

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
