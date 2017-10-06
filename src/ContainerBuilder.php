<?php

use Unity\Component\Container\Container;
use Unity\Component\Container\Dependency\DependencyFactory;
use Unity\Component\Container\Factories\BindResolverFactory;
use Unity\Component\Container\Factories\DependencyResolverFactory;
use Unity\Reflector\Reflector;

/**
 * Class ContainerBuilder.
 *
 * Container builder.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class ContainerBuilder
{
    public static function build()
    {
        $dependencyFactory = new DependencyFactory(new Reflector());
        $dependencyResolverFactory = new DependencyResolverFactory();
        $bindResolverFactory = new BindResolverFactory();

        $container = new Container(
            $dependencyFactory,
            $dependencyResolverFactory,
            $bindResolverFactory
        );

        return $container;
    }
}
