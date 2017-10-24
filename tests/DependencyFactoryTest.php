<?php

use Helpers\Foo;
use Helpers\Bar;
use Helpers\IFoo;
use Helpers\Foobar;
use Helpers\Bounded;
use Helpers\Mocks\TestBase;
use Helpers\WithConstructor;
use e200\MakeAccessible\Make;
use Unity\Reflector\Reflector;
use Helpers\WithConstructorParameters;
use Unity\Contracts\Container\Bind\IBindResolver;
use Unity\Component\Container\Dependency\DependencyFactory;
use Unity\Component\Container\Exceptions\ClassNotFoundException;
use Unity\Component\Container\Exceptions\NonInstantiableClassException;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyFactoryTest extends TestBase
{
    /**
     * Performs a test giving required constructor parameters directly.
     *
     * @covers DependencyFactory::giveConstructorArgs()
     */
    public function testGetConstructorArgsWithGivenDependencies()
    {
        $df = $this->getAccessibleDependencyFactory();

        $refClass = new ReflectionClass(WithConstructorParameters::class);

        $args = [1, 2];

        $constructorArgs = $df->getConstructorArgs($refClass, $args, []);

        $this->assertInternalType('array', $constructorArgs);
        $this->assertEquals($args, $constructorArgs);
    }

    /**
     * Performs a test with a class that needs their dependencies to be auto resolved.
     *
     * @covers DependencyFactory::giveConstructorArgs()
     */
    public function testGetConstructorArgsWithAutoResolver()
    {
        $df = $this->getAccessibleDependencyFactory();

        $refClass = new ReflectionClass(Foo::class);

        /////////////////////////////////////////////////////////////////////////////////
        // Since we're testing autowiring, there's no need to give explicit arguments. //
        /////////////////////////////////////////////////////////////////////////////////
        $constructorArgs = $df->getConstructorArgs($refClass, [], []);

        $this->assertInstanceOf(Bar::class, $constructorArgs[0]);
    }

    /**
     * Performs a test with a class that needs their dependencies to be auto resolved.
     *
     * @covers DependencyFactory::giveConstructorArgs()
     */
    public function testGetConstructorArgsWithAutoResolverDisabled()
    {
        $df = $this->getAccessibleDependencyFactory(false);

        $refClass = new ReflectionClass(Foo::class);

        /////////////////////////////////////////////////////////////////////////////////
        // Since we're testing autowiring, there's no need to give explicit arguments. //
        /////////////////////////////////////////////////////////////////////////////////
        $constructorArgs = $df->getConstructorArgs($refClass, [], []);

        $this->assertEmpty($constructorArgs);
    }
    
    public function testGetConstructorArgsWithBinds()
    {
        $bindResolverMock = $this->createMock(IBindResolver::class);
        
        $bindResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->willReturn(true);

        ////////////////////////////////////////////////////////////////
        // Since make is public, we don't need to make it accessible. //
        ////////////////////////////////////////////////////////////////
        
        $df = $this->getAccessibleDependencyFactory();

        $refClass = new ReflectionClass(Foobar::class);

        /////////////////////////////////////////////////////////////////////////////////
        // Since we're testing autowiring, there's no need to give explicit arguments. //
        /////////////////////////////////////////////////////////////////////////////////
        $constructorArgs = $df->getConstructorArgs($refClass, [], [IFoo::class => $bindResolverMock]);

        $this->assertTrue($constructorArgs[0]);
    }

    public function testMake()
    {
        ////////////////////////////////////////////////////////////////
        // Since make is public, we don't need to make it accessible. //
        ////////////////////////////////////////////////////////////////
        $df = $this->getDependencyFactory(false);

        $this->assertInstanceOf(WithConstructor::class, $df->make(WithConstructor::class));
    }

    public function testInnerMake()
    {
        ////////////////////////////////////////////////////////////////
        // Since make is public, we don't need to make it accessible. //
        ////////////////////////////////////////////////////////////////
        $df = $this->getAccessibleDependencyFactory();

        $this->assertInstanceOf(WithConstructor::class, $df->innerMake(WithConstructor::class));
    }

    public function testClassNotFoundExceptionOnInnerMake()
    {
        $this->expectException(ClassNotFoundException::class);
        ////////////////////////////////////////////////////////////////
        // Since make is public, we don't need to make it accessible. //
        ////////////////////////////////////////////////////////////////
        $df = $this->getAccessibleDependencyFactory();

        $df->innerMake(null);
    }

    /**
     * @covers DependencyFactory::make()
     */
    public function testNonInstantiableClassExceptionOnMake()
    {
        $this->expectException(NonInstantiableClassException::class);

        $df = $this->getDependencyFactory();

        $df->make(IFoo::class);
    }

    public function getDependencyFactory($autoResolve = true, $canUseAnnotations = false)
    {
        return new DependencyFactory($autoResolve, $canUseAnnotations, new Reflector());
    }

    public function getAccessibleDependencyFactory($autoResolve = true, $canUseAnnotations = false)
    {
        return Make::accessible($this->getDependencyFactory($autoResolve, $canUseAnnotations));
    }
}
