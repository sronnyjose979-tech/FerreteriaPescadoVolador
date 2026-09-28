<?php

namespace App\Exceptions;

use Exception;

class StockInsuficienteException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 409);
    }
}