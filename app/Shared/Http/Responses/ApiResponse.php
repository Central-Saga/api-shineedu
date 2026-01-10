<?php

namespace App\Shared\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\MessageBag;

final class ApiResponse
{
    /**
     * Base response builder (do not call directly in controllers unless needed).
     */
    public static function respond(
        bool $success,
        string $message,
        mixed $data = null,
        mixed $errors = null,
        array $meta = [],
        int $status = 200,
        array $headers = [],
        int $jsonOptions = 0
    ): JsonResponse {
        $payload = [
            'success' => $success,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $payload['data'] = $data;
        }

        if (!is_null($errors)) {
            $payload['errors'] = self::normalizeErrors($errors);
        }

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status, $headers, $jsonOptions);
    }

    /**
     * 200 OK
     */
    public static function ok(mixed $data = null, string $message = 'OK'): JsonResponse
    {
        return self::respond(true, $message, $data, null, [], 200);
    }

    /**
     * 201 Created
     */
    public static function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return self::respond(true, $message, $data, null, [], 201);
    }

    /**
     * 202 Accepted (async / queued)
     */
    public static function accepted(mixed $data = null, string $message = 'Accepted'): JsonResponse
    {
        return self::respond(true, $message, $data, null, [], 202);
    }

    /**
     * 204 No Content (usually for delete). Note: JSON body should be empty for 204.
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * 400 Bad Request
     */
    public static function badRequest(string $message = 'Bad Request', mixed $errors = null): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 400);
    }

    /**
     * 401 Unauthorized
     */
    public static function unauthorized(string $message = 'Unauthorized', mixed $errors = null): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 401);
    }

    /**
     * 403 Forbidden
     */
    public static function forbidden(string $message = 'Forbidden', mixed $errors = null): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 403);
    }

    /**
     * 404 Not Found
     */
    public static function notFound(string $message = 'Not Found', mixed $errors = null): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 404);
    }

    /**
     * 409 Conflict (duplicate, state conflict)
     */
    public static function conflict(string $message = 'Conflict', mixed $errors = null): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 409);
    }

    /**
     * 422 Unprocessable Entity (validation)
     */
    public static function validation(mixed $errors, string $message = 'Validation Error'): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 422);
    }

    /**
     * 429 Too Many Requests
     */
    public static function tooManyRequests(string $message = 'Too Many Requests', mixed $errors = null): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 429);
    }

    /**
     * 500 Internal Server Error
     */
    public static function serverError(string $message = 'Server Error', mixed $errors = null): JsonResponse
    {
        return self::respond(false, $message, null, $errors, [], 500);
    }

    /**
     * Generic error with custom status code.
     */
    public static function fail(
        string $message = 'Error',
        int $status = 400,
        mixed $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(false, $message, null, $errors, $meta, $status);
    }

    /**
     * Paginated response helper.
     * Works with Resource::collection($paginator) or any data.
     */
    public static function paginated(
        mixed $data,
        LengthAwarePaginator $paginator,
        string $message = 'OK',
        array $extraMeta = []
    ): JsonResponse {
        $meta = array_merge([
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
        ], $extraMeta);

        // If passed a JsonResource collection, it will be serialized by JsonResponse
        return self::respond(true, $message, $data, null, $meta, 200);
    }

    /**
     * Convenience: wrap a single resource.
     */
    public static function resource(JsonResource $resource, string $message = 'OK', int $status = 200): JsonResponse
    {
        return self::respond(true, $message, $resource, null, [], $status);
    }

    /**
     * Normalize errors into consistent structure.
     * Accepts: array, MessageBag, string, Throwable-ish objects, etc.
     */
    private static function normalizeErrors(mixed $errors): mixed
    {
        if ($errors instanceof MessageBag) {
            return $errors->toArray();
        }

        if (is_array($errors)) {
            return $errors;
        }

        if (is_string($errors)) {
            return ['message' => $errors];
        }

        // Last resort: try to stringify
        return ['message' => (string) $errors];
    }
}
