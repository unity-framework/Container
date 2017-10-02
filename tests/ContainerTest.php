<?php

use e200\MakeAccessible\Make;
use Helpers\Mocks\TestBase;
use Unity\Component\Container\Container;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class ContainerTest extends TestBase
{
    /**
     * Tests if `Container::register()` adds `DependencyResolver` instances into
     * `Container::$resolvers` and returns the new `DependencyResolver` instance.
     */
    public function testRegister()
    {
        $resolverId = 'itemId';
        $expectation = 'Jay Alpha - Visions';

        $container = $this->getContainer();
        $accessibleContainer = Make::accessible($container);

        $dependencyResolver = $container->register($resolverId, $expectation);

        $this->assertInstanceOf(DependencyResolver::class, $dependencyResolver);
        $this->assertEquals($dependencyResolver, $accessibleContainer->resolvers[$resolverId]);
    }

    public function testHas()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->has('id'));
        $this->assertInstanceOf(
            DependencyResolver::class,
            $container->register('id', 'value')
        );
        $this->assertTrue($container->has('id'));
    }

    /*
        public function testUnregister()
        {
            $container = $this->getContainer();
    
            $container->register('id', 'value');
            $this->assertTrue($container->has('id'));
            $container->unregister('id');
            $this->assertFalse($container->has('id'));
        }
    
    
        public function testHasGetRegisterDataForType()
        {
            $container = $this->getContainer();
    
            $this->assertFalse($container->hasBind(IFoo::class));
            $instance = $container->bind(IFoo::class, function () {
                return new Foo(new Bar());
            });
    
            $this->assertInstanceOf(IContainer::class, $instance);
            $data = $container->getBoundValue(IFoo::class);
    
            $this->assertInstanceOf(IFoo::class, $data);
        }
    
    
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
    
        public  function testIsBound()
        {
            $container = $this->getContainer();
    
            $this->assertFalse($container->isBound(''));
        }
    
    
        public function testGetBoundValue()
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
    
    
        public function testNotFoundExceptionOnGetBind()
        {
            $this->expectException(NotFoundException::class);
    
            $container = $this->getContainer();
    
            $this->assertFalse($container->isBound(''));
            $container->getBoundValue('');
        }
    
    
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
    
        public function testMakeWithParameters()
        {
            $container = $this->getContainer();
    
            $container->register('withParams', WithConstructorParameters::class);
    
            $instance = $container->make('withParams', ['', '']);
    
            $this->assertInstanceOf(WithConstructorParameters::class, $instance);
        }
    
    
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
    
        public function testEnableCanAutowiring()
        {
            $container = $this->getContainer();
    
            // True by default.
            $this->assertTrue($container->canAutowiring());
            $container->enableAutowiring(false);
            $this->assertFalse($container->canAutowiring());
            $container->enableAutowiring(true);
            $this->assertTrue($container->canAutowiring());
        }
    
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
    
        public function testArrayAccess()
        {
            $container = $this->getContainer();
    
            $this->assertInstanceOf(ArrayAccess::class, $container);
    
            $this->assertArrayNotHasKey('e200', $container);
    
            $container['e200'] = 'Eleandro Duzentos';
    
            $this->assertArrayHasKey('e200', $container);
    
            $this->assertEquals('Eleandro Duzentos', $container['e200']);
        }*/

    public function getContainer()
    {
        $dependencyFactoryMock = $this->mockDependencyFactory();

        $instance = new Container();

        $instance->setDependencyFactory($dependencyFactoryMock);

        return $instance;
    }

    public function getAccessibleContainer($container)
    {
        return Make::accessible($container);
    }
}
