<?php

namespace Unity\Component\Container\Factories;

use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Contracts\Container\Dependency\IDependencyFactory;
use Unity\Contracts\Container\Factories\IBindResolverFactory;
use Unity\Contracts\Container\Factories\IDependencyResolverFactory;
use Unity\Contracts\Container\IContainer;

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
        IBindResolverFactory $bindResolverFactory,
        IContainer         $container
    ) {
        return new DependencyResolver(
            $entry,
            $dependencyFactory,
            $bindResolverFactory,
            $container
        );
    }
}
