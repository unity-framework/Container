<?php

namespace Unity\Component\Container\Factories;

use Unity\Component\Container\Contracts\Dependency\IDependencyFactory;
use Unity\Component\Container\Contracts\Factories\IBindResolverFactory;
use Unity\Component\Container\Contracts\Factories\IDependencyResolverFactory;
use Unity\Component\Container\Contracts\IContainer;
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
