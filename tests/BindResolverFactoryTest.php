<?php

use Unity\Component\Container\Contracts\Bind\IBindResolver;
use Unity\Component\Container\Factories\BindResolverFactory;
use Unity\Tests\Container\TestBase;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolverFactoryTest extends TestBase
{
    public function testMake()
    {
        $container = $this->mockContainer();

        $this->assertInstanceOf(
            IBindResolver::class,
            (new BindResolverFactory())->make(
                function () {
                },
                $container
            )
        );
    }
}
