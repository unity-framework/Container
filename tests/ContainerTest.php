<?php

use e200\MakeAccessible\Make;
use Helpers\Mocks\TestBase;
use Unity\Component\Container\Container;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Contracts\IServiceProvider;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;

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

    public function testBind()
    {
        $container = $this->getContainer();

        $returnedInstance = $container->bind('id', function () {
        });

        $this->assertInstanceOf(Container::class, $returnedInstance);

        $accessibleContainer = Make::accessible($container);

        $this->assertArrayHasKey('id', $accessibleContainer->binds);
    }

    public function testIsBound()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->isBound('id'));

        $container->bind('id', function () {
        });

        $this->assertTrue($container->isBound('id'));
    }

    public function testGetBoundValue()
    {
        $container = $this->getContainer();

        $container->bind('id', function () {
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

        $container = $this->getContainer($dependencyFactory);

        /*******************************************************
         * Must be a string or the `DependencyResolverFactory` *
         * will not be called                                  *
         *******************************************************/
        $container->set('id', '');

        $instance1 = $container->make('id');

        $this->assertTrue(true);
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

        ////////////////////
        // Samething here //
        ////////////////////
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
            public function register() : array
            {
                return [
                    ['id1' => null],
                    ['id2' => null],
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
                public function register() : array
                {
                    return [
                        ['id1' => null],
                        ['id2' => null],
                    ];
                }
            },
            new class() implements IServiceProvider {
                public function register() : array
                {
                    return [
                        ['id3' => null],
                        ['id4' => null],
                    ];
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

        $bindResolverFactory = $this->mockBindResolverFactory();

        return new Container(
            $dependencyFactoryMock,
            $dependencyResolverFactoryMock,
            $bindResolverFactory
        );
    }
}
