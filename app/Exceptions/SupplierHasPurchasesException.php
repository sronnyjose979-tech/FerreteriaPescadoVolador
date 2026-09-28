<?php

namespace App\Exceptions;

use Exception;

class SupplierHasPurchasesException extends Exception
{
    public function __construct(
        string $message = 'No se puede eliminar el proveedor porque tiene compras asociadas.'
    ) {
        parent::__construct($message, 409);
    }
}