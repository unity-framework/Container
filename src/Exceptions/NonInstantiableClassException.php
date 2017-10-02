<?php

namespace Unity\Component\Container\Exceptions;

use Exception;
use Throwable;
use Psr\Container\ContainerExceptionInterface;

/**
 * Class NonInstantiableClassException.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class NonInstantiableClassException extends Exception implements ContainerExceptionInterface
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
