<?php

namespace Unity\Component\Container\Exceptions;

use Exception;
use Throwable;
use Psr\Container\ContainerExceptionInterface;

/**
 * Class DuplicateDependencyNameException.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DuplicateIdException extends Exception implements ContainerExceptionInterface
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
