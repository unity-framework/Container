<?php

namespace Unity\Component\IoC\Exceptions;

use Exception;
use Throwable;

class MissingConstructorArgumentException extends Exception implements MissingConstructorArgumentExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}