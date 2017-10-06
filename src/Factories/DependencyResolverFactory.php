<?php

namespace Unity\Component\Container\Factories;

use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Contracts\IDependencyFactory;
use Unity\Component\Container\Contracts\IDependencyResolverFactory;
use Unity\Component\Container\Dependency\DependencyResolver;

/**
 * Class DependencyResolverFactory.
 *
 * Makes DependencyResolver instances.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverFactory implements IDependencyResolverFactory
{
    public function make(
        $entry,
        IDependencyFactory $dependencyFactory,
        IContainer         $container
    ) {
        return new DependencyResolver(
            $entry,
            $dependencyFactory,
            $container
        );
    }
}
