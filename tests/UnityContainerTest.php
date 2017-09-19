<?php

use Helpers\Bar;
use Helpers\Foo;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;
use Unity\Component\Container\UnityContainer;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class UnityContainerTest extends TestCase
{
    public function testRegisterHas()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->has('id'));
        $this->assertInstanceOf(
            DependencyResolver::class,
            $container->register('id', 'value')
        );
        $this->assertTrue($container->has('id'));
    }

    public function testUnregister()
    {
        $container = $this->getContainer();

        $container->register('id', 'value');
        $this->assertTrue($container->has('id'));
        $container->unregister('id');
        $this->assertFalse($container->has('id'));
    }

    public function testDuplicateDependencyResolverException()
    {
        $this->expectException(DuplicateIdException::class);

        $container = $this->getContainer();

        $container->register('id', null);
        $container->register('id', null);
    }

    public function testGet()
    {
        $container = $this->getContainer();

        $container->register('bar', Bar::class);

        $instance = $container->get('bar');

        $this->assertInstanceOf(Bar::class, $instance);
        $this->assertSame($instance, $container->get('bar'));
    }

    public function testGetNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $container->get(null);
    }

    public function testMake()
    {
        $container = $this->getContainer();

        $container->register('bar', Bar::class);

        $instance1 = $container->make('bar');
        $instance2 = $container->make('bar');

        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testMakeNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $container->make(null);
    }

    public function testReplace()
    {
        $container = $this->getContainer();

        $container->register('bar', Bar::class);
        $instance1 = $container->get('bar');

        $container->replace('bar', Foo::class);
        $instance2 = $container->get('bar');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testSetGetResolver()
    {
        $dependencyResolverMock = $this->createMock(IDependencyResolver::class);

        $container = $this->getContainer();

        $dependencyResolver = $container->setDependencyResolver('id', $dependencyResolverMock);

        $this->assertSame($dependencyResolverMock, $dependencyResolver);
        $this->assertSame($dependencyResolverMock, $container->getDependencyResolver('id'));
    }

    public function getContainer()
    {
        return new UnityContainer();
    }
}
