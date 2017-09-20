<?php

use Helpers\Bar;
use Helpers\Foo;
use Helpers\Foobar;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;
use Unity\Component\Container\Container;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class ContainerTest extends TestCase
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

    /**
     * Checks if the container throws an exception
     * if someone tries to register 2 dependencies with
     * the same id.
     */
    public function testDuplicateDependencyResolverExceptionOnRegister()
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

    /**
     * Checks if the container throws an exception if
     * someone tries to get a dependency that does'nt exists.
     */
    public function testNotFoundExceptionOnGet()
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

    /**
     * Checks if the container throws an exception if
     * someone tries to make a dependency that does'nt exists.
     */
    public function testNotFoundExceptionOnMake()
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

    /**
     * @covers Container::enableAutoInject()
     * @covers Container::canAutoInject()
     */
    public function testSetGetResolver()
    {
        $dependencyResolverMock = $this->createMock(IDependencyResolver::class);

        $container = $this->getContainer();

        $dependencyResolver = $container->setDependencyResolver('id', $dependencyResolverMock);

        $this->assertSame($dependencyResolverMock, $dependencyResolver);
        $this->assertSame($dependencyResolverMock, $container->getDependencyResolver('id'));
    }

    /**
     * @covers Container::throwNotFoundException()
     */
    public function testThrowNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $container->throwNotFoundException(null);
    }

    public function testEnableCanAutoInject()
    {
        $container = $this->getContainer();

        /*
         * True by default.
         */
        $this->assertTrue($container->canAutoInject());
        $container->enableAutoInject(false);
        $this->assertFalse($container->canAutoInject());
        $container->enableAutoInject(true);
        $this->assertTrue($container->canAutoInject());
    }

    public function testGetDependencyThatNeedsAnObjectOfTypeInterface()
    {
        $this->markTestSkipped();

        $container = $this->getContainer();

        $container->register('foobar', Foobar::class);
        $container->get('foobar');
    }

    public function getContainer()
    {
        return new Container();
    }
}
