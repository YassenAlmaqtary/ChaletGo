<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    protected function successResponse($data = null, string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message ?? __('messages.success'),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message = null, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? __('messages.error'),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors, string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('messages.validation_error'), 422, $errors);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('messages.not_found'), 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('messages.unauthorized'), 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('messages.forbidden'), 403);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse($data, string $message = 'تم جلب البيانات بنجاح'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages(),
            ]
        ]);
    }
}
