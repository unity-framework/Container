<?php

use Helpers\Mocks\TestBase;
use Unity\Component\Container\Contracts\IBindResolver;
use Unity\Component\Container\Factories\BindResolverFactory;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolverFactoryTest extends TestBase
{
    function testMake()
    {
        $container = $this->mockContainer();

        $this->assertInstanceOf(
            IBindResolver::class,
            (new BindResolverFactory())->make(
                function () {},
                $container
            )
        );
    }
}
