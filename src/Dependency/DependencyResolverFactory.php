<?php

namespace Unity\Component\Container\Dependency;

use Unity\Component\Container\Contracts\IUnityContainer;

/**
 * Class DependencyResolverFactory.
 *
 * @package Unity\Component\Container\Dependency
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverFactory
{
    static function Make($id, $entry, IUnityContainer $container)
    {
        return new DependencyResolver($id, $entry, $container);
    }
}
