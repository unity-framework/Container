<?php

namespace Unity\Component\Container\Dependency;

use Unity\Component\Container\Contracts\IContainer;

/**
 * Class DependencyResolverFactory.
 *
 * Makes DependencyResolver::class instances
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverFactory
{
    public static function Make($id, $entry, IContainer $container)
    {
        return new DependencyResolver($id, $entry, $container);
    }
}
