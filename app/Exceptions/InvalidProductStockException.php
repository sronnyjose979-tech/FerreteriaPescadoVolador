<?php

namespace App\Exceptions;

class InvalidProductStockException extends BusinessException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}