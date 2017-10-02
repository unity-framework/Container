<?php

use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\DocBlock\Tag;
use Helpers\Mocks\TestBase;
use e200\MakeAccessible\Make;
use Unity\Reflector\Reflector;
use Unity\Component\Container\Exceptions\NonInstantiableClassException;
use Unity\Component\Container\Dependency\DependencyFactory;
use Helpers\Bar;
use Helpers\Foo;
use Helpers\IFoo;
use Helpers\Bounded;
use Helpers\WithProperties;
use Helpers\WithConstructor;
use Helpers\WithConstructorParameters;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyFactoryTest extends TestBase
{
    function testGetTagValue()
    {
        $df = $this->getAccessibleDependencyFactory();

        $tagMock = $this->createMock(Tag::class);

        $tagMock
            ->expects($this->once())
            ->method('render')
            ->willReturn('@var \Unity\Reflector');

        $this->assertEquals('\Unity\Reflector', $df->getTagValue($tagMock));
    }

    /**
     * @covers DependencyFactory::getTagValue()
     */
    function testGetTagValueWithNoValue()
    {
        $df = $this->getAccessibleDependencyFactory();

        $tagMock = $this->createMock(Tag::class);

        $tagMock
            ->expects($this->exactly(4))
            ->method('render')
            ->will($this->onConsecutiveCalls('@var', '', 'var', 'var '));

        $this->assertFalse($df->getTagValue($tagMock));
        $this->assertFalse($df->getTagValue($tagMock));
        $this->assertFalse($df->getTagValue($tagMock));
        $this->assertFalse($df->getTagValue($tagMock));
    }

    /**
     * Performs a test giving required constructor parameters directly.
     *
     * @covers DependencyFactory::giveConstructorArgs()
     */
    function testGetConstructoArgsWithGivenDependencies()
    {
        $df = $this->getAccessibleDependencyFactory();

        $refClass = new ReflectionClass(WithConstructorParameters::class);

        $args = [1, 2];

        $constructorArgs = $df->getConstructorArgs($args, $refClass);

        $this->assertInternalType('array', $constructorArgs);
        $this->assertEquals($args, $constructorArgs);
    }



    /**
     * Performs a test with a class that has its constructor parameters bounded
     * in the Container.
     *
     * @covers DependencyFactory::giveConstructorArgs()
     */
    function testGetConstructoArgsWithBoundArgs()
    {
        $containerMock = $this->mockContainer();

        /************************************************************************************
         * We need to tell to `DependencyFactory` that the `Bound` class constructor        *
         * parameter `Container::isBound()` and then give the `Container::getBoundValue()`. *
         ************************************************************************************/
        $containerMock
            ->expects($this->once())
            ->method('isBound')
            ->willReturn(true);

        $containerMock
            ->expects($this->once())
            ->method('getBoundValue')
            ->willReturn(new Bar());

        $df = $this->getAccessibleDependencyFactory($containerMock);

        $refClass = new ReflectionClass(Bounded::class);

        $constructorArgs = $df->getConstructorArgs([], $refClass);

        $this->assertInstanceOf(Bar::class, $constructorArgs[0]);
    }

    /**
     * Performs a test with a class that needs their dependencies to be autowired.
     *
     * Autowiring is the process of search for dependencies and try to resolve
     * them automatically.
     *
     * @covers DependencyFactory::giveConstructorArgs()
     */
    function testGetConstructoArgsWithAutowiring()
    {
        $containerMock = $this->mockContainer();

        ///////////////////////////////////////////////////////////////////////////////
        // We need to tell to `DependencyFactory` that `Container::canAutowiring()`. //
        ///////////////////////////////////////////////////////////////////////////////
        $containerMock
            ->expects($this->once())
            ->method('canAutowiring')
            ->willReturn(true);

        /////////////////////////////////////
        // Our testing method is protected //
        /////////////////////////////////////
        $df = $this->getAccessibleDependencyFactory($containerMock);

        $refClass = new ReflectionClass(Foo::class);

        /////////////////////////////////////////////////////////////////////////////////
        // Since we're testing autowiring, there's no need to give explicit arguments. //
        /////////////////////////////////////////////////////////////////////////////////
        $constructorArgs = $df->getConstructorArgs([], $refClass);

        $this->assertInstanceOf(Bar::class, $constructorArgs[0]);
    }

    /**
     * Performs a test with a class that needs their property dependencies to be
     * autowired using annotations (@inject).
     *
     * @covers DependencyFactory::injectPropertyDependencies()
     */
    function testInjectPropertyDependencies()
    {
        $containerMock = $this->mockContainer();

        ////////////////////////////////////////////////////////////////////////////////
        // We need to tell to DependencyFactory that `Container::canUseAnnotations()` //
        ////////////////////////////////////////////////////////////////////////////////
        $containerMock
            ->expects($this->exactly(2))
            ->method('canUseAnnotations')
            ->willReturn(true);

        //////////////////////////////////////
        // Getting our accessible instance. //
        //////////////////////////////////////
        $df = $this->getAccessibleDependencyFactory($containerMock);

        /////////////////////////////////////////////////////////////////////
        // This is the instance that need their properties to be injected. //
        /////////////////////////////////////////////////////////////////////
        $instance = new WithProperties();

        $refClass = new ReflectionClass($instance);

        //////////////////////////
        // Our testing function //
        //////////////////////////
        $df->injectPropertyDependencies($refClass, $instance);

        //////////////////////////////////////////////////////////////////////////////
        // Since `WithProperties` propeties are protected, we make them accessible. //
        //////////////////////////////////////////////////////////////////////////////
        $accessibleWithPropeties = Make::accessible($instance);

        $this->assertInstanceOf(Bar::class, $accessibleWithPropeties->bar);
        $this->assertInstanceOf(stdClass::class, $accessibleWithPropeties->std);
    }

    function testMake()
    {
        $containerMock = $this->mockContainer();

        ////////////////////////////////////////////////////////////////////////////////
        // We need to tell to DependencyFactory that `Container::canUseAnnotations()` //
        ////////////////////////////////////////////////////////////////////////////////
        $containerMock
            ->expects($this->once())
            ->method('canUseAnnotations')
            ->willReturn(true);

        ////////////////////////////////////////////////////////////////
        // Since make is public, we don't need to make it accessible. //
        ////////////////////////////////////////////////////////////////
        $df = $this->getDependencyFactory($containerMock);

        $this->assertInstanceOf(WithConstructor::class, $df->make(WithConstructor::class));
    }



    /**
     * @covers DependencyFactory::make()
     */
    function testNonInstantiableClassExceptionOnMake()
    {
        $this->expectException(NonInstantiableClassException::class);

        $df = $this->getDependencyFactory();

        $df->make(IFoo::class);
    }

    function getDependencyFactory($container = null)
    {
        //////////////////////////////////////////////////////////
        // Some test functions need to provide their own mocks. //
        //////////////////////////////////////////////////////////
        if(!$container) {
            $container = $this->mockContainer();
        }

        return new DependencyFactory($container, new Reflector());
    }

    function getAccessibleDependencyFactory($container = null)
    {
        return Make::accessible($this->getDependencyFactory($container));
    }
}
