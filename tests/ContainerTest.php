<?php

use Helpers\Bar;
use Helpers\Fake;
use Helpers\Foo;
use Helpers\Foobar;
use Helpers\IFoo;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IContainer;
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
     * @covers Container::bind()
     * @covers Container::hasBind()
     * @covers Container::getBind()
     */
    public function testHasGetRegisterDataForType()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->hasBind(IFoo::class));
        $instance = $container->bind(IFoo::class, function (){
            return new Foo(new Bar());
        });

        $this->assertInstanceOf(IContainer::class, $instance);
        $data = $container->getBind(IFoo::class);

        $this->assertInstanceOf(IFoo::class, $data);
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

    public  function testHasBind()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->hasBind(''));
    }

    /**
     * @covers Container::bind()
     */
    public function testGetBind()
    {
        $container = $this->getContainer();

        $container->register('foo', Foo::class);
        $container->bind(IFoo::class, function ($container) {
            return $container->get('foo');
        });

        $container->register('foobar', Foobar::class);
        $instance = $container->get('foobar');

        $this->assertInstanceOf(Foobar::class, $instance);
    }

    /**
     * @covers Container::getBind()
     */
    public function testNotFoundExceptionOnGetBind()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $this->assertFalse($container->hasBind(''));
        $container->getBind('');
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
     * @covers Container::throwNotFoundException()
     */
    public function testThrowNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $fake = new Fake(new Container());

        $fake->throwNotFoundException(null);
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

    /**
     * @covers Container::__call()
     */
    public function test__setget()
    {
        $container = $this->getContainer();

        $container->bar = Bar::class;

        $this->assertInstanceOf(Bar::class, $container->bar);
    }

    public function testCount()
    {
        $container = $this->getContainer();

        $this->assertInstanceOf(Countable::class, $container);

        $container->register('a', null);
        $container->register('b', null);

        $this->assertCount(2, $container);
    }

    /**
     * @covers Container::__call()
     */
    public function testArrayAccess()
    {
        $container = $this->getContainer();

        $this->assertInstanceOf(ArrayAccess::class, $container);

        $this->assertArrayNotHasKey('e200', $container);

        $container['e200'] = 'Eleandro Duzentos';

        $this->assertArrayHasKey('e200', $container);

        $this->assertEquals('Eleandro Duzentos', $container['e200']);
    }

    public function getContainer()
    {
        return new Container();
    }
}
