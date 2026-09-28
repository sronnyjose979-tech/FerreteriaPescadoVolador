<?php

namespace App\Exceptions;

class ProductHasDependenciesException extends BusinessException
{
    public function __construct(
        string $message = 'No se puede eliminar el producto porque tiene dependencias activas.'
    ) {
        parent::__construct($message);
    }
}
