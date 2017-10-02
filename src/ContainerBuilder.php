<?php

use Unity\Component\Container\Container;
use Unity\Reflector\Reflector;

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
