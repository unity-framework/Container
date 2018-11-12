<?php

namespace Unity\Component\Container\Contracts\Factories;

use Closure;
use Unity\Component\Container\Contracts\IContainer;

/**
 * Interface IBindResolverFactory.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IBindResolverFactory
{
    public function make(Closure $callback, IContainer $container);
}
