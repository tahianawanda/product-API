<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Support\Facades\Log;


trait HandlesApiExceptions
{
    public function handleException(\Throwable $e): array
    {
        // Registrar la excepción en el log
        Log::error('Exception captured: ', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $statusCode = match (true) {
            $e instanceof ValidationException => 422,
            $e instanceof ModelNotFoundException => 404,
            $e instanceof BadRequestException => 400,
            default => 500,
        };

        $message = match (true) {
            $e instanceof ValidationException => 'Validation failed.',
            $e instanceof ModelNotFoundException => 'Resource not found.',
            $e instanceof BadRequestException => 'Bad request. Please check your input.',
            default => 'An unexpected error occurred.',
        };

        $errorDetails = $e->getMessage() ?: 'No additional error details are available.';

        return [
            'status_code' => $statusCode,
            'data' => [
                'success' => false,
                'message' => $message,
                'errors' => ['details' => $errorDetails],
            ],
        ];
    }
}
