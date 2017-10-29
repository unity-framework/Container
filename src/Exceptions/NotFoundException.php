<?php

namespace Unity\Component\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * Class NotFoundException.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
