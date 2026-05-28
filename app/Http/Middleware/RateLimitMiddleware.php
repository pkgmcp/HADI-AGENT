<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('ai.rate_limiting.enabled', true)) {
            return $next($request);
        }

        $key = $this->getKey($request);
        $maxRequests = config('ai.rate_limiting.max_requests_per_minute', 60);

        $current = Cache::get($key, 0);

        if ($current >= $maxRequests) {
            Log::warning('Rate limit exceeded', [
                'key' => $key,
                'requests' => $current,
                'limit' => $maxRequests,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => 60,
            ], 429);
        }

        // Use Cache::add for atomic first-creation, increment for subsequent
        if (!Cache::has($key)) {
            Cache::add($key, 1, 60);
            Cache::add("{$key}_tokens", $request->input('estimated_tokens', 0), 60);
        } else {
            Cache::increment($key);
            Cache::increment("{$key}_tokens", $request->input('estimated_tokens', 0));
        }

        $response = $next($request);

        $remaining = max(0, $maxRequests - Cache::get($key, 0));

        $response->headers->set('X-RateLimit-Limit', $maxRequests);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', time() + 60);

        return $response;
    }

    private function getKey(Request $request): string
    {
        return 'rate_limit:' . ($request->user()?->id ?? $request->ip());
    }
}
