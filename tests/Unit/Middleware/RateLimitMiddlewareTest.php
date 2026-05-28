<?php

use App\Http\Middleware\RateLimitMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['ai.rate_limiting.enabled' => true]);
    config(['ai.rate_limiting.max_requests_per_minute' => 5]);

    Cache::flush();
});

it('allows request under limit', function () {
    $request = Request::create('/api/v1/health', 'GET');
    $request->setUserResolver(fn() => null);

    $middleware = new RateLimitMiddleware;
    $response = $middleware->handle($request, fn($req) => response('OK'));

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->has('X-RateLimit-Limit'))->toBeTrue();
    expect($response->headers->get('X-RateLimit-Remaining'))->toBe('5');
});

it('blocks request over limit', function () {
    $request = Request::create('/api/v1/health', 'GET');
    $request->setUserResolver(fn() => null);

    $middleware = new RateLimitMiddleware;

    // Use up all requests
    for ($i = 0; $i < 5; $i++) {
        $middleware->handle($request, fn($req) => response('OK'));
    }

    // Next one should be blocked
    $response = $middleware->handle($request, fn($req) => response('OK'));

    expect($response->getStatusCode())->toBe(429);
    expect($response->getData()->success)->toBeFalse();
});

it('skips rate limiting when disabled', function () {
    config(['ai.rate_limiting.enabled' => false]);

    $request = Request::create('/test', 'GET');
    $middleware = new RateLimitMiddleware;
    $response = $middleware->handle($request, fn($req) => response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

it('sets rate limit reset header', function () {
    $request = Request::create('/api/v1/status', 'GET');
    $request->setUserResolver(fn() => null);

    $middleware = new RateLimitMiddleware;
    $response = $middleware->handle($request, fn($req) => response('OK'));

    expect($response->headers->has('X-RateLimit-Reset'))->toBeTrue();
    expect((int) $response->headers->get('X-RateLimit-Reset'))->toBeGreaterThan(time() - 1);
});

it('differentiates limits by ip', function () {
    $req1 = Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);
    $req1->setUserResolver(fn() => null);

    $req2 = Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => '2.2.2.2']);
    $req2->setUserResolver(fn() => null);

    $middleware = new RateLimitMiddleware;

    // Exhaust 1.1.1.1
    for ($i = 0; $i < 5; $i++) {
        $middleware->handle($req1, fn($r) => response('OK'));
    }

    // 2.2.2.2 should still be allowed
    $response = $middleware->handle($req2, fn($r) => response('OK'));
    expect($response->getStatusCode())->toBe(200);
});
