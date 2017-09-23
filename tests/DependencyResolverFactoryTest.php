<?php

use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Dependency\DependencyResolverFactory;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverFactoryTest extends TestCase
{
    public function testMake()
    {
        $containerMock = $this->createMock(IContainer::class);

        $this->assertInstanceOf(
            DependencyResolver::class,
            DependencyResolverFactory::make('testId', 'e200', $containerMock)
        );
    }
}
