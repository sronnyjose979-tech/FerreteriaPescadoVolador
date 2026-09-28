<?php

namespace App\Exceptions;

use Exception;

class ProductHasDependenciesException extends Exception
{
    public function __construct(
        string $message = 'No se puede eliminar el producto porque tiene dependencias activas.'
    ) {
        parent::__construct($message, 409);
    }
}