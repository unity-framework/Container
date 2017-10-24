<?php

use Helpers\Mocks\TestBase;
use Unity\Component\Container\Factories\DependencyResolverFactory;
use Unity\Contracts\Container\Dependency\IDependencyResolver;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverFactoryTest extends TestBase
{
    public function testMake()
    {
        $container = $this->mockContainer();
        $dependencyFactory = $this->mockDependencyFactory();
        $bindResolverFactory = $this->mockBindResolverFactory();

        $this->assertInstanceOf(
            IDependencyResolver::class,
            (new DependencyResolverFactory())->make(
                null,
                $dependencyFactory,
                $bindResolverFactory,
                $container
            )
        );
    }
}
