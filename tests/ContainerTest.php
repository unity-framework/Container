<?php

use e200\MakeAccessible\Make;
use Helpers\Mocks\TestBase;
use Unity\Component\Container\Container;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;
use Unity\Contracts\Container\Dependency\IDependencyResolver;
use Unity\Contracts\Container\IContainer;
use Unity\Contracts\Container\IServiceProvider;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class ContainerTest extends TestBase
{
    public function testHas()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->has('id'));

        $container->set('id', null);

        $this->assertTrue($container->has('id'));
    }

    /**
     * Tests if `Container::set()` adds `DependencyResolver` instances into
     * `Container::$resolvers` and returns the new `DependencyResolver` instance.
     */
    public function testSet()
    {
        $container = $this->getContainer();
        $accessibleContainer = Make::accessible($container);

        $dependencyResolver = $container->set('id', null);

        $this->assertInstanceOf(IDependencyResolver::class, $dependencyResolver);
        $this->assertArrayHasKey('id', $accessibleContainer->resolvers);
        $this->assertEquals($dependencyResolver, $accessibleContainer->resolvers['id']);
    }

    /**
     * Tests if `Container::unset()` removes `DependencyResolver` instances from
     * `Container::$resolvers` `Container` instance.
     */
    public function testUnset()
    {
        $container = $this->getContainer();
        $accessibleContainer = Make::accessible($container);

        $container->set('id', null);
        $returnedInstance = $container->unset('id');

        $this->assertArrayNotHasKey('id', $accessibleContainer->resolvers);
        $this->assertInstanceOf(Container::class, $returnedInstance);
    }

    /**
     * Tests if `Container::replace()` replaces `DependencyResolver` instances into
     * `Container::$resolvers` `Container` instance.
     */
    public function tesReplace()
    {
        $container = $this->getContainer();

        $dependencyResolver1 = $container->set('id', null);
        $dependencyResolver2 = $container->replace('id', null);

        $this->assertInstanceOf(IDependencyResolver::class, $dependencyResolver1);
        $this->assertInstanceOf(IDependencyResolver::class, $dependencyResolver2);
        $this->assertNotSame($dependencyResolver1, $dependencyResolver2);
    }

    /**
     * @covers Container::set()
     */
    public function testDuplicateDependencyResolverExceptionOnRegister()
    {
        $this->expectException(DuplicateIdException::class);

        $container = $this->getContainer();

        $container->set('id', null);
        $container->set('id', null);
    }

    public function testGet()
    {
        $container = $this->getContainer();

        $container->set('id', true);

        $this->assertTrue($container->get('id'));
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

        $container = $this->getContainer($dependencyFactory);

        /*******************************************************
         * Must be a string or the `DependencyResolverFactory` *
         * will not be called                                  *
         *******************************************************/
        $container->set('id', true);

        $value = $container->make('id');

        $this->assertTrue($value);
    }

    public function testMakeWithParameters()
    {
        $container = $this->getContainer();

        /*
            The value doesn't matter, it will never been returned because
            since we're using a mock of the `DependencyFactory`, but it
            must be a string, because the container only calls the
            `DependencyFactory` if it is a string
         */
        $container->set('id', null);

        /////////////////////
        // Same thing here //
        /////////////////////
        $instance = $container->make('id', []);

        $this->assertTrue($instance);
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

    public function testSetServiceProvider()
    {
        $container = $this->getContainer();

        $container->setServiceProvider(new class() implements IServiceProvider {
            public function register(IContainer $container)
            {
                return [
                    'id1' => null,
                    'id2' => null,
                ];
            }
        });

        ////////////////////////////////////////////
        // Checking if at least 2 were registered //
        ////////////////////////////////////////////
        $this->assertTrue($container->has('id1'));
        $this->assertTrue($container->has('id2'));

        $this->assertCount(2, $container);
    }

    public function testSetServiceProviders()
    {
        $container = $this->getContainer();

        $container->setServiceProviders([
            new class() implements IServiceProvider {
                public function register(IContainer $container)
                {
                    return ['id1' => null, 'id2' => null];
                }
            },
            new class() implements IServiceProvider {
                public function register(IContainer $container)
                {
                    return ['id3' => null, 'id4' => null];
                }
            },
        ]);

        ////////////////////////////////////////////
        // Checking if at least 2 were registered //
        ////////////////////////////////////////////
        $this->assertTrue($container->has('id1'));
        $this->assertTrue($container->has('id2'));

        $this->assertCount(4, $container);
    }

    /**
     * @covers Container::__set()
     * @covers Container::__get()
     */
    public function test__setget()
    {
        $container = $this->getContainer();

        ///////////////////////////////
        // This value doesn't matter //
        ///////////////////////////////
        $container->bar = null;

        $this->assertTrue($container->bar);
    }

    /**
     * @covers Container::count()
     */
    public function testCount()
    {
        $container = $this->getContainer();

        $this->assertInstanceOf(Countable::class, $container);

        $container->set('a', null);
        $container->set('b', null);

        $this->assertCount(2, $container);
    }

    public function testArrayAccess()
    {
        $container = $this->getContainer();

        $this->assertInstanceOf(ArrayAccess::class, $container);

        $this->assertArrayNotHasKey('e200', $container);

        ///////////////////////////////
        // This value doesn't matter //
        ///////////////////////////////
        $container['e200'] = null;

        $this->assertArrayHasKey('e200', $container);

        $this->assertTrue($container['e200']);
    }

    public function getContainer($dependencyFactoryMock = null, $dependencyResolverFactoryMock = null)
    {
        if (!$dependencyFactoryMock) {
            $dependencyFactoryMock = $this->mockDependencyFactory();
        }

        if (!$dependencyResolverFactoryMock) {
            $dependencyResolverFactoryMock = $this->mockDependencyResolverFactory();
        }

        $bindResolverFactoryMock = $this->mockBindResolverFactory();

        return new Container(
            $dependencyFactoryMock,
            $dependencyResolverFactoryMock,
            $bindResolverFactoryMock
        );
    }
}
