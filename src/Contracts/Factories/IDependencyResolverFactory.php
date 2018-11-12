<?php

namespace Unity\Component\Container\Contracts\Factories;

use Unity\Component\Container\Contracts\Dependency\IDependencyFactory;
use Unity\Component\Container\Contracts\IContainer;

/**
 * Interface IDependencyResolverFactory.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IDependencyResolverFactory
{
    public function make(
        $entry,
        IDependencyFactory $dependencyFactory,
        IBindResolverFactory $bindResolverFactory,
        IContainer $container
    );
}
