<?php

namespace Unity\Tests\Container\Helpers;

use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Contracts\IServiceProvider;

class ServiceProvider implements IServiceProvider
{
    public function register(IContainer $container)
    {
        $container->set('id1', null);
        $container->set('id2', null);
    }
}
