<?php

namespace Unity\Component\Container\Exceptions;

use Exception;
use Throwable;
use Unity\Component\Container\Contracts\IDuplicateIdException;

/**
 * Class DuplicateDependencyNameException.
 *
 * @package Unity\Component\Container\Exceptions
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DuplicateIdException extends Exception implements IDuplicateIdException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}