<?php

use Helpers\Bar;
use Helpers\Foo;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;
use Unity\Component\Container\UnityContainer;
use PHPUnit\Framework\TestCase;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class UnityContainerTest extends TestCase
{
    function testRegisterHas()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->has('id'));
        $this->assertInstanceOf(
            DependencyResolver::class,
            $container->register('id', 'value')
        );
        $this->assertTrue($container->has('id'));
    }

    function testUnregister()
    {
        $container = $this->getContainer();

        $container->register('id', 'value');
        $this->assertTrue($container->has('id'));
        $container->unregister('id');
        $this->assertFalse($container->has('id'));
    }

    function testDuplicateDependencyResolverException()
    {
        $this->expectException(DuplicateIdException::class);

        $container = $this->getContainer();

        $container->register('id', null);
        $container->register('id', null);
    }

    function testGet()
    {
        $container = $this->getContainer();

        $container->register('bar', Bar::class);

        $instance = $container->get('bar');

        $this->assertInstanceOf(Bar::class, $instance);
        $this->assertSame($instance, $container->get('bar'));
    }

    function testGetNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $container->get(null);
    }

    function testMake()
    {
        $container = $this->getContainer();

        $container->register('bar', Bar::class);

        $instance1 = $container->make('bar');
        $instance2 = $container->make('bar');

        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    function testMakeNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $container->make(null);
    }

    function testReplace()
    {
        $container = $this->getContainer();

        $container->register('bar', Bar::class);
        $instance1 = $container->get('bar');

        $container->replace('bar', Foo::class);
        $instance2 = $container->get('bar');

        $this->assertNotSame($instance1, $instance2);
    }

    function testSetGetResolver()
    {
        $dependencyResolverMock = $this->createMock(IDependencyResolver::class);

        $container = $this->getContainer();

        $dependencyResolver = $container->setDependencyResolver('id', $dependencyResolverMock);

        $this->assertSame($dependencyResolverMock, $dependencyResolver);
        $this->assertSame($dependencyResolverMock, $container->getDependencyResolver('id'));
    }

    function getContainer()
    {
        return new UnityContainer();
    }
}
