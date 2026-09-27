<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessException extends Exception
{
    /**
     * Violación de una regla de negocio. Por defecto se traduce a 409 Conflict,
     * como pide el Laboratorio 5.
     *
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        string $message = '',
        public int $statusCode = 409,
        public array $errors = []
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        $payload = [
            'message' => $this->getMessage(),
        ];

        if (! empty($this->errors)) {
            $payload['errors'] = $this->errors;
        }

        return response()->json($payload, $this->statusCode);
    }
}
