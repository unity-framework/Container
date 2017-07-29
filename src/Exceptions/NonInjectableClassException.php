<?php

namespace Unity\Component\IoC\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NonInjectableClassException
 * @package Unity\Component\IoC\Exceptions
 */
class NonInjectableClassException extends \Exception implements NotFoundExceptionInterface
{

}