<?php

namespace App\Support;

use Illuminate\Http\JsonResponse as BaseJsonResponse;

class JsonResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): BaseJsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(string $message = 'Error', int $code = 400, mixed $errors = null): BaseJsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    public static function created(mixed $data = null, string $message = 'Created successfully'): BaseJsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function notFound(string $message = 'Resource not found'): BaseJsonResponse
    {
        return self::error($message, 404);
    }

    public static function validationError(mixed $errors, string $message = 'Validation failed'): BaseJsonResponse
    {
        return self::error($message, 422, $errors);
    }

    public static function serverError(string $message = 'Internal server error'): BaseJsonResponse
    {
        return self::error($message, 500);
    }
}
