<?php

namespace Unity\Tests\Container\Helpers;

use Unity\Contracts\Container\IContainer;
use Unity\Contracts\Container\IServiceProvider;

class ServiceProvider implements IServiceProvider
{
    public function register(IContainer $container)
    {
        $container->set('id1', null);
        $container->set('id2', null);
    }
}
