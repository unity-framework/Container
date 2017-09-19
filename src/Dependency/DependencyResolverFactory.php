<?php

namespace Unity\Component\Container\Dependency;

use Unity\Component\Container\Contracts\IUnityContainer;

/**
 * Class DependencyResolverFactory.
 *
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverFactory
{
    public static function Make($id, $entry, IUnityContainer $container)
    {
        return new DependencyResolver($id, $entry, $container);
    }
}
