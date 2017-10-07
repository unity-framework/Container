<?php

use Helpers\Mocks\TestBase;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Factories\DependencyResolverFactory;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverFactoryTest extends TestBase
{
    public function testMake()
    {
        $container = $this->mockContainer();
        $dependencyFactory = $this->mockDependencyFactory();

        $this->assertInstanceOf(
            IDependencyResolver::class,
            (new DependencyResolverFactory())->make(
                null,
                $dependencyFactory,
                $container
            )
        );
    }
}
