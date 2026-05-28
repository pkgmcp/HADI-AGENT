<?php

use App\Support\JsonResponse;

it('returns success response', function () {
    $response = JsonResponse::success(['key' => 'value'], 'Operation successful');

    expect($response->getStatusCode())->toBe(200);
    expect($response->getData()->success)->toBeTrue();
    expect($response->getData()->message)->toBe('Operation successful');
    expect($response->getData()->data->key)->toBe('value');
});

it('returns success with default message', function () {
    $response = JsonResponse::success(['foo' => 'bar']);

    expect($response->getData()->message)->toBe('Success');
});

it('returns error response', function () {
    $response = JsonResponse::error('Something failed', 400);

    expect($response->getStatusCode())->toBe(400);
    expect($response->getData()->success)->toBeFalse();
    expect($response->getData()->message)->toBe('Something failed');
});

it('returns error with validation errors', function () {
    $errors = ['email' => ['The email field is required.']];
    $response = JsonResponse::error('Validation failed', 422, $errors);

    expect($response->getStatusCode())->toBe(422);
    expect($response->getData()->errors)->toBe($errors);
});

it('returns created response', function () {
    $response = JsonResponse::created(['id' => 1]);

    expect($response->getStatusCode())->toBe(201);
    expect($response->getData()->message)->toBe('Created successfully');
});

it('returns not found response', function () {
    $response = JsonResponse::notFound('User not found');

    expect($response->getStatusCode())->toBe(404);
    expect($response->getData()->message)->toBe('User not found');
});

it('returns validation error response', function () {
    $errors = ['name' => ['Name is required']];
    $response = JsonResponse::validationError($errors);

    expect($response->getStatusCode())->toBe(422);
    expect($response->getData()->errors)->toBe($errors);
});

it('returns server error response', function () {
    $response = JsonResponse::serverError();

    expect($response->getStatusCode())->toBe(500);
    expect($response->getData()->message)->toBe('Internal server error');
});

it('returns server error with custom message', function () {
    $response = JsonResponse::serverError('Database connection failed');

    expect($response->getData()->message)->toBe('Database connection failed');
});
