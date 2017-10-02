<?php

use e200\MakeAccessible\Make;
use Helpers\Bar;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Dependency\DependencyFactory;
use Unity\Component\Container\Dependency\DependencyResolver;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverTest extends TestCase
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

    public function testSetSingleton()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $dependencyResolver->setSingleton(200);

        $this->assertEquals(200, $dependencyResolver->singleton);
    }

    public function testGetSingleton()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $dependencyResolver->singleton = 200;

        $this->assertEquals(200, $dependencyResolver->getSingleton());
    }

    public function testHasSingleton()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $this->assertFalse($dependencyResolver->hasSingleton());

        $dependencyResolver->singleton = 200;

        $this->assertTrue($dependencyResolver->hasSingleton());
    }

    public function testMakeWithClassName()
    {
        $dependencyFactory = $this->getDependencyFactoryMock();

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

    public function testMakeWithCallback()
    {
        $dependencyResolver = $this->getAccessibleResolver();

        $dependencyResolver->entry = function () { return new Bar(); };

        $instance = $dependencyResolver->make();

        $this->assertInstanceOf(Bar::class, $instance);
    }

    public function testMakeWithValue()
    {
        $dependencyResolver = $this->getDependencyResolver('e200');

        $value = $dependencyResolver->make();

        $this->assertEquals('e200', $value);
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

        $containerMock
            ->expects($this->any())
            ->method('canAutowiring')
            ->willReturn(true);

        return $containerMock;
    }

    public function getDependencyFactoryMock()
    {
        $dependencyFactory = $this->createMock(DependencyFactory::class);

        return $dependencyFactory;
    }

    /**
     * @param mixed  $entry The resolver entry.
     * @param object $dependencyFactory The dependencyFactory dependency.
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
         * provide their own specific mockups using the `$dependencyFactory`
         * argument.
         */
        $dependencyFactory = $dependencyFactory ?? $this->getDependencyFactoryMock();

        return new DependencyResolver(
            $entry,
            $dependencyFactory,
            $containerMock
        );
    }

    /**
     *
     *
     * @param mixed  $entry The resolver entry.
     * @param object $dependencyFactory The dependencyFactory dependency.
     */
    public function getAccessibleResolver($entry = null, $dependencyFactory = null)
    {
        $resolver = $this->getDependencyResolver($entry, $dependencyFactory);

        return Make::accessible($resolver);
    }
}
