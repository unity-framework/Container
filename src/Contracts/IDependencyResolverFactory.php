<?php

namespace Unity\Component\Container\Contracts;

/**
 * Interface IDependencyResolverFactory.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IDependencyResolverFactory
{
    public function make($entry, IDependencyFactory $dependencyFactory, IContainer $container);
}
