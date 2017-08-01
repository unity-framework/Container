<?php

namespace Unity\Component\IoC\Exceptions;

use Throwable;

class DuplicateResolverNameException extends \Exception implements DuplicateResolverNameExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}