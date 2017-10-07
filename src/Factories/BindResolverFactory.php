<?php

namespace Unity\Component\Container\Factories;

use Closure;
use Unity\Component\Container\Bind\BindResolver;
use Unity\Component\Container\Contracts\IBindResolverFactory;
use Unity\Component\Container\Contracts\IContainer;

/**
 * Class BindResolverFactoryFactory.
 *
 * Makes BindResolverFactory instances.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolverFactory implements IBindResolverFactory
{
    public function make(Closure $callback, IContainer $container)
    {
        return new BindResolver($callback, $container);
    }
}
