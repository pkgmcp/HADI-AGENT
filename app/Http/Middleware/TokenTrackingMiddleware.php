<?php

namespace App\Http\Middleware;

use App\Services\MCP\TokenObserver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenTrackingMiddleware
{
    private TokenObserver $tokenObserver;

    public function __construct(TokenObserver $tokenObserver)
    {
        $this->tokenObserver = $tokenObserver;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->header('X-Session-Id')
            ?? $request->input('session_id')
            ?? 'anon_' . md5($request->ip() . $request->userAgent());

        $request->merge(['_session_id' => $sessionId]);

        $response = $next($request);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $sessionId = $request->input('_session_id');

        if ($sessionId && config('mcp.token_tracking.enabled', true)) {
            $usage = $response->headers->get('X-Token-Usage');

            if ($usage) {
                $data = json_decode($usage, true);
                $this->tokenObserver->track(
                    provider: $data['provider'] ?? 'unknown',
                    model: $data['model'] ?? 'unknown',
                    inputTokens: $data['input_tokens'] ?? 0,
                    outputTokens: $data['output_tokens'] ?? 0,
                    cost: $data['cost'] ?? 0,
                    latencyMs: $data['latency_ms'] ?? 0,
                    sessionId: $sessionId,
                    action: $request->path(),
                );
            }
        }
    }
}
