<?php

use e200\MakeAccessible\Make;
use Helpers\Mocks\TestBase;
use Unity\Component\Container\Container;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;
use Helpers\Bar;
use Helpers\WithConstructorParameters;

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

        $container = $this->getContainer();
        $accessibleContainer = Make::accessible($container);

        $dependencyResolver = $container->register($resolverId, null);

        $this->assertInstanceOf(DependencyResolver::class, $dependencyResolver);
        $this->assertEquals($dependencyResolver, $accessibleContainer->resolvers[$resolverId]);
    }

    /**
     * Tests if `Container::unregister()` removes `DependencyResolver` instances from
     * `Container::$resolvers` `Container` instance.
     */
    public function testUnregister()
    {
        $resolverId = 'itemId';

        $container = $this->getContainer();
        $accessibleContainer = Make::accessible($container);

        $dependencyResolver = $container->register($resolverId, null);
        $returnedInstance = $dependencyResolver = $container->unregister($resolverId);

        $this->assertArrayNotHasKey($resolverId, $accessibleContainer->resolvers);
        $this->assertInstanceOf(Container::class, $returnedInstance);
    }

    /**
     * Tests if `Container::replace()` replaces `DependencyResolver` instances into
     * `Container::$resolvers` `Container` instance.
     */
    public function tesReplace()
    {
        $resolverId = 'itemId';

        $container = $this->getContainer();

        $dependencyResolver1 = $container->register($resolverId, null);
        $dependencyResolver2 = $container->replace($resolverId);

        $this->assertInstanceOf(DependencyResolver::class, $dependencyResolver1);
        $this->assertInstanceOf(DependencyResolver::class, $dependencyResolver2);
        $this->assertNotSame($dependencyResolver1, $dependencyResolver2);
    }

    public function testHas()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->has('id'));

        $container->register('id', null);

        $this->assertTrue($container->has('id'));
    }

    /**
     * @covers Container::register()
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

        $container->register('bar', true);

        $this->assertTrue($container->get('bar'));
    }

    public function testBind()
    {
        $container = $this->getContainer();

        $returnedInstance = $container->bind('id', function() {});

        $this->assertInstanceOf(Container::class, $returnedInstance);

        $accessibleContainer = Make::accessible($container);

        $this->assertArrayHasKey('id', $accessibleContainer->binds);
    }

    public function testIsBound()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->isBound('id'));

        $container->bind('id', function() {});

        $this->assertTrue($container->isBound('id'));
    }

    public function testGetBoundValue()
    {
        $container = $this->getContainer();

        $container->bind('id', function () {
            return true;
        });

        $this->assertTrue($container->getBoundValue('id'));
    }

    /**
     * @covers Container::getBoundValue()
     */
    public function testNotFoundExceptionOnGetBind()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $this->assertFalse($container->isBound(''));
        $container->getBoundValue('');
    }

    /**
     * @covers Container::Get()
     */
    public function testNotFoundExceptionOnGet()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $container->get(null);
    }

    public function testMake()
    {
        $dependencyFactory = $this->mockDependencyFactory();

        $dependencyFactory
            ->expects($this->exactly(2))
            ->method('make')
            ->will($this->onConsecutiveCalls(new Bar(), new Bar()));

        $container = $this->getContainer($dependencyFactory);

        $container->register('bar', Bar::class);

        $instance1 = $container->make('bar');
        $instance2 = $container->make('bar');

        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertInstanceOf(Bar::class, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testMakeWithParameters()
    {
        $this->markTestSkipped();

        $dependencyFactory = $this->mockDependencyFactory();

        $dependencyFactory
            ->expects($this->once())
            ->method('make')
            ->willReturn(new WithConstructorParameters());

        $container = $this->getContainer($dependencyFactory);

        $container->register('withParams', WithConstructorParameters::class);

        $instance = $container->make('withParams', ['', '']);

        $this->assertInstanceOf(WithConstructorParameters::class, $instance);
    }

    /**
     * @covers Container::make()
     */
    public function testNotFoundExceptionOnMake()
    {
        $this->expectException(NotFoundException::class);

        $container = $this->getContainer();

        $container->make(null);
    }

    public function testEnableAutowiring()
    {
        $container = $this->getContainer();
        $accessibleContainer = Make::accessible($container);

        $container->enableAutowiring(false);
        $this->assertFalse($accessibleContainer->canAutowiring);

        $container->enableAutowiring(true);
        $this->assertTrue($accessibleContainer->canAutowiring);
    }

    public function testCanAutowiring()
    {
        $container = $this->getContainer();
        $accessibleContainer = Make::accessible($container);

        // True by default.
        $this->assertTrue($container->canAutowiring());

        $accessibleContainer->canAutowiring = false;
        $this->assertFalse($container->canAutowiring());

        $container->enableAutowiring(true);
        $accessibleContainer->canAutowiring = true;
        $this->assertTrue($container->canAutowiring());
    }

    /**
     * @covers Container::__set()
     * @covers Container::__get()
     */
    public function test__setget()
    {
        $expected = 'Tay Devenny - Leitha Feat. Stanley Ipkuss (Prod. Wun Two)';

        $container = $this->getContainer();

        $container->bar = $expected;

        $this->assertEquals($expected, $container->bar);
    }

    /**
     * @covers Container::count()
     */
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
    }

    public function getContainer($dependencyFactoryMock = null)
    {
        if (!$dependencyFactoryMock) {
            $dependencyFactoryMock = $this->mockDependencyFactory();
        }

        $instance = new Container();

        $instance->setDependencyFactory($dependencyFactoryMock);

        return $instance;
    }
}
