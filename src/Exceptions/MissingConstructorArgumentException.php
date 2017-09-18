<?php

namespace Unity\Component\Container\Exceptions;

use Exception;
use Throwable;
use Unity\Component\Container\Contracts\IMissingConstructorArgumentException;

/**
 * Class MissingConstructorArgumentException.
 *
 * @package Unity\Component\Container\Exceptions
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class MissingConstructorArgumentException extends Exception implements IMissingConstructorArgumentException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}