<?php

namespace Unity\Component\Container\Contracts;

use Closure;

/**
 * Interface IBindResolverFactory.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IBindResolverFactory
{
    public function make(Closure $callback, IContainer $container);
}
