<?php

use Unity\Reflector\Reflector;
use Unity\Component\Container\Container;

class ContainerBuilder
{
    public static function build()
    {
        $container = new Container();

        $dependencyFactory = $self::getDependencyFactory($container);

        $container->setDependencyFactory($dependencyFactory);

        return $container;
    }

    public static function getDependencyFactory(Container $container)
    {
        return new DependencyFactory($container, new Reflector());
    }
}
