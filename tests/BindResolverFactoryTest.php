<?php

use Helpers\Mocks\TestBase;
use Unity\Component\Container\Factories\BindResolverFactory;
use Unity\Contracts\Container\Bind\IBindResolver;

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
