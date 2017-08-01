<?php

namespace Unity\Component\IoC\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ContainerException extends Exception implements NotFoundExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}