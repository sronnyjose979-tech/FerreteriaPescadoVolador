<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessException extends Exception
{
    public function __construct(
        string $message = '',
        public int $statusCode = 422,
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
