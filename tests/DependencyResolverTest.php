<?php

use Helpers\Bar;
use Helpers\IFoo;
use Helpers\Mocks\TestBase;
use e200\MakeAccessible\Make;
use Unity\Contracts\Container\IContainer;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Contracts\Container\Dependency\IDependencyResolver;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverTest extends TestBase
{
    public function testGetEntry()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $this->assertEquals(Bar::class, $dependencyResolver->getEntry());
    }

    public function testGive()
    {
        $realDependencyResolver = $this->getDependencyResolver();

        $dependencyResolver = Make::accessible($realDependencyResolver);

        $arguments = [0, 1];

        $instance = $realDependencyResolver->give($arguments);

        $this->assertSame($arguments, $dependencyResolver->arguments);
        $this->assertSame($realDependencyResolver, $instance);
    }

    public function testGetArguments()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $arguments = [0, 1];

        $dependencyResolver->arguments = $arguments;

        $this->assertSame($arguments, $dependencyResolver->getArguments());
    }

    public function testBind()
    {
        $dependencyResolver = $this->getAccessibleResolver();
        
        $instance = $dependencyResolver->bind(Bar::class, function () {});

        $this->assertInstanceOf(IDependencyResolver::class, $instance);
        
        $this->assertArrayHasKey(Bar::class, $dependencyResolver->binds);
        $this->assertTrue($dependencyResolver->binds[Bar::class]);
    }

    public function testGetBinds()
    {
        $dependencyResolver = $this->getAccessibleResolver();
        
        $dependencyResolver->binds = true;

        $this->assertTrue($dependencyResolver->getBinds());
    }

    public function testSetSingleton()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $dependencyResolver->setSingleton(true);

        $this->assertTrue($dependencyResolver->singleton);
    }

    public function testGetSingleton()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $dependencyResolver->singleton = true;

        $this->assertTrue($dependencyResolver->getSingleton());
    }

    public function testHasSingleton()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $this->assertFalse($dependencyResolver->hasSingleton());

        $dependencyResolver->singleton = 200;

        $this->assertTrue($dependencyResolver->hasSingleton());
    }

    public function testProtect()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $this->assertFalse($dependencyResolver->protectEntry);

        $dependencyResolver->protect();
        $this->assertTrue($dependencyResolver->protectEntry);
        $dependencyResolver->protect(false);
        $this->assertFalse($dependencyResolver->protectEntry);
        $dependencyResolver->protect(true);
        $this->assertTrue($dependencyResolver->protectEntry);
    }

    public function testMakeWithClassName()
    {
        $dependencyFactory = $this->mockDependencyFactory();

        $dependencyFactory
            ->expects($this->exactly(2))
            ->method('make')
            ->will($this->onConsecutiveCalls(new Bar(), new Bar()));

        $dependencyResolver = $this->getAccessibleResolver(null, $dependencyFactory);

        $dependencyResolver->entry = Bar::class;

        $instance1 = $dependencyResolver->make();
        $instance2 = $dependencyResolver->make();

        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testMakeWithInterface()
    {
        $dependencyFactory = $this->mockDependencyFactory();

        $dependencyResolver = $this->getAccessibleResolver(null, $dependencyFactory);

        $expected = IFoo::class;

        $dependencyResolver->entry = $expected;

        $value = $dependencyResolver->make();
        
        $this->assertEquals($expected, $value);
    }

    public function testMakeWithCallback()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $dependencyResolver->entry = function () {
            return new Bar();
        };

        $instance = $dependencyResolver->make();

        $this->assertInstanceOf(Bar::class, $instance);
    }

    public function testMakeWithValue()
    {
        $dependencyResolver = $this->getDependencyResolver('e200');

        $value = $dependencyResolver->make();

        $this->assertEquals('e200', $value);
    }

    public function testMakeWithProtectedResolver()
    {
        $dependencyResolver = $this->getAccessibleResolver(function () {
            return '3.1415923565897931';
        });

        //////////////////////////////////////
        // Setting the protectEntry as true //
        //////////////////////////////////////
        $dependencyResolver->protectEntry = true;

        $value = $dependencyResolver->make();

        $this->assertNotEquals('3.1415923565897931', $value);
        $this->assertInternalType('callable', $value);
    }

    /*public function testResolve()
    {
        $dependencyResolver = $this->getDependencyResolver();

        $instance = $dependencyResolver->resolve();

        $this->assertInstanceOf(Bar::class, $instance);
    }*/

    public function getContainerMock()
    {
        $containerMock = $this->createMock(IContainer::class);

        return $containerMock;
    }

    /**
     * @param mixed  $entry             The resolver entry.
     * @param object $dependencyFactory The dependencyFactory dependency.
     *
     * @return DependencyResolver
     */
    public function getDependencyResolver($entry = null, $dependencyFactory = null)
    {
        // `Container` is a DependencyResolver dependency.
        $containerMock = $this->getContainerMock();

        /*
         * By default, the test entry is a string containing a class name.
         *
         * But some tests needs to pass a specific `$entry`, in such case we let
         * these methods provide their own entries using the `$entry` argument.
         */
        $entry = $entry ?? Bar::class;

        /*
         * `DependencyFactory` is a DependencyResolver dependency.
         *
         * Some tests also needs to pass a `$dependencyFactory` mock with
         * a specific behaviour, in such case we let these methods
         * provide their own mocks using the `$dependencyFactory`
         * argument.
         */
        $dependencyFactory = $dependencyFactory ?? $this->mockDependencyFactory();

        $bindResolverFactory = $this->mockBindResolverFactory();

        return new DependencyResolver(
            $entry,
            $dependencyFactory,
            $bindResolverFactory,
            $containerMock
        );
    }

    /**
     * @param mixed  $entry             The resolver entry.
     * @param object $dependencyFactory The dependencyFactory dependency.
     *
     * @return \e200\MakeAccessible\AccessibleInstance
     */
    public function getAccessibleResolver($entry = null, $dependencyFactory = null)
    {
        $resolver = $this->getDependencyResolver($entry, $dependencyFactory);

        return Make::accessible($resolver);
    }
}
