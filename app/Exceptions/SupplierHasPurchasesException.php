<?php

namespace App\Exceptions;

class SupplierHasPurchasesException extends BusinessException
{
    public function __construct(
        string $message = 'No se puede eliminar el proveedor porque tiene compras asociadas.'
    ) {
        parent::__construct($message);
    }
}
