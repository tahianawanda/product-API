<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->resource['success'],
            'message' => $this->resource['message'] ?? 'An error occurred.',
            'errors' => $this->formatErrors($this->resource['errors'] ?? null),
        ];
    }

    /**
     * Formatea los errores en un formato consistente.
     *
     * @param mixed $errors
     * @return array
     */
    protected function formatErrors($errors): array
    {
        if (is_string($errors) && !empty($errors)) {
            return ['details' => $errors];
        }

        if (is_array($errors) && !empty($errors)) {
            return $errors; // Devuelve los errores sin modificar
        }

        return ['details' => 'No additional error details are available.'];
    }
}
