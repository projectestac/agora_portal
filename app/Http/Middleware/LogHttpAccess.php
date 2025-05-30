<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\HttpAccessLog;
use Illuminate\Support\Facades\Auth;

class LogHttpAccess
{
    // Maximum length for the payload.
    public const TRUNCATE_LENGTH = 5000;

    /**
     * @throws \JsonException
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->except(['_token']);

        // For confidentiality, do not log passwords nor password confirmations.
        if (isset($input['password'])) {
            $input['password'] = '***';
        }
        if (isset($input['password_confirmation'])) {
            $input['password_confirmation'] = '***';
        }

        $payload = json_encode($input, JSON_THROW_ON_ERROR);
        $payload = strlen($payload) > $this::TRUNCATE_LENGTH
            ? substr($payload, 0, $this::TRUNCATE_LENGTH) . '...[truncated]'
            : $payload;

        $user = Auth::user();
        $username = $user?->name;
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        HttpAccessLog::create([
            'accessed_at' => now(),
            'ip'          => $request->ip(),
            'user_agent'  => $request->header('User-Agent'),
            'url'         => $request->fullUrl(),
            'method'      => $request->method(),
            'payload'     => $payload,
            'session'     => $sessionId,
            'username'    => $username
        ]);

        return $next($request);
    }
}
