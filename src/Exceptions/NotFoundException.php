<?php

namespace Unity\Component\Container\Exceptions;

use Exception;
use Throwable;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException.
 *
 * @package Unity\Component\Container\Exceptions
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}