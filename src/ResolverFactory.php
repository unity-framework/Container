<?php

namespace Unity\Component\IoC;

class ResolverFactory
{
    static function Make($name, $entry, ContainerContract $container)
    {
        return new Resolver($name, $entry, $container);
    }
}