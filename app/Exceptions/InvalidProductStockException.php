<?php

namespace App\Exceptions;

use Exception;

class InvalidProductStockException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 409);
    }
}