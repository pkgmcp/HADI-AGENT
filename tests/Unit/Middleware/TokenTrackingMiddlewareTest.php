<?php

use App\Http\Middleware\TokenTrackingMiddleware;
use App\Services\MCP\TokenObserver;
use Illuminate\Http\Request;

it('injects session id from header', function () {
    $request = Request::create('/api/v1/mcp/messages', 'POST');
    $request->headers->set('X-Session-Id', 'header_session');

    $middleware = new TokenTrackingMiddleware(app(TokenObserver::class));

    $middleware->handle($request, function ($req) {
        expect($req->input('_session_id'))->toBe('header_session');
        return response('OK');
    });
});

it('injects session id from request body', function () {
    $request = Request::create('/api/v1/agents/test/execute', 'POST', [
        'session_id' => 'body_session',
    ]);

    $middleware = new TokenTrackingMiddleware(app(TokenObserver::class));

    $middleware->handle($request, function ($req) {
        expect($req->input('_session_id'))->toBe('body_session');
        return response('OK');
    });
});

it('generates anonymous session id when none provided', function () {
    $request = Request::create('/api/v1/health', 'GET');

    $middleware = new TokenTrackingMiddleware(app(TokenObserver::class));

    $middleware->handle($request, function ($req) {
        expect($req->input('_session_id'))->toStartWith('anon_');
        return response('OK');
    });
});

it('prefers header session id over body', function () {
    $request = Request::create('/post', 'POST', ['session_id' => 'body_id']);
    $request->headers->set('X-Session-Id', 'header_id');

    $middleware = new TokenTrackingMiddleware(app(TokenObserver::class));

    $middleware->handle($request, function ($req) {
        expect($req->input('_session_id'))->toBe('header_id');
        return response('OK');
    });
});

it('attaches session id to request for downstream use', function () {
    $request = Request::create('/api/v1/workflows', 'POST');

    $middleware = new TokenTrackingMiddleware(app(TokenObserver::class));

    $middleware->handle($request, function ($req) {
        expect($req->has('_session_id'))->toBeTrue();
        return response('OK');
    });
});
