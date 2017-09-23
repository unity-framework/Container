<?php

use Helpers\Bar;
use Helpers\Foo;
use Helpers\Fake;
use Helpers\WithConstructor;
use Helpers\WithConstructorDependencies;
use Helpers\WithConstructorParameterBind;
use Helpers\WithConstructorParameters;
use Helpers\WithoutConstructor;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Dependency\DependencyBuilder;
use Unity\Component\Container\Exceptions\MissingConstructorArgumentException;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyBuilderTest extends TestCase
{
    /**
     * @covers DependencyBuilder::getGivenParameter()
     * @covers DependencyBuilder::hasConstructorData()
     * @covers DependencyBuilder::getGivenParameters()
     */
    public function testGetHasConstructorData()
    {
        $fake = $this->getFakeClass();

        $this->assertFalse($fake->hasConstructorData('name'));

        $fake = $this->getFakeClass(null, ['name' => 'Lorem Ipsum']);

        $this->assertTrue($fake->hasConstructorData('name'));
        $name = $fake->getConstructorData('name');

        $this->assertEquals('Lorem Ipsum', $name);
    }
    public function testReflectClass()
    {
        $fake = $this->getFakeClass();

        $rc = $fake->reflectClass(Bar::class);
        $this->assertInstanceOf(ReflectionClass::class, $rc);
    }

    public function testHasConstructor()
    {
        $fake = $this->getFakeClass();

        $rc = $this->reflectClassForTest(WithoutConstructor::class);
        $this->assertFalse($fake->hasConstructor($rc));
        $rc = $this->reflectClassForTest(WithConstructor::class);
        $this->assertTrue($fake->hasConstructor($rc));
    }

    public function testHasParametersOnConstructor()
    {
        $fake = $this->getFakeClass();

        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $this->assertEquals(true, $fake->hasParametersOnConstructor($rc));
        $rc = $this->reflectClassForTest(WithConstructor::class);
        $this->assertEquals(false, $fake->hasParametersOnConstructor($rc));
    }

    public function testGetConstructorParameters()
    {
        $fake = $this->getFakeClass();

        $rc = $this->reflectClassForTest(WithConstructorDependencies::class);
        $params = $fake->getConstructorParameters($rc);
        $this->assertInternalType('array', $params);
        $this->assertCount(2, $params);
        $this->assertInstanceOf(ReflectionParameter::class, $params[0]);
        $this->assertInstanceOf(ReflectionParameter::class, $params[1]);
    }

    /**
     * @covers DependencyBuilder::getConstructorParametersData()
     */
    public function testGetConstructorParametersData()
    {
        $fake = $this->getFakeClass(null, [
            'param1' => 1,
            'param2' => 1,
        ]);

        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $params = $rc->getConstructor()->getParameters();
        $paramsValues = $fake->getConstructorParametersData($params);

        $this->assertTrue(is_array($paramsValues));
        $this->assertCount(2, $paramsValues);
        $this->assertEquals(1, $paramsValues['param1']);
        $this->assertEquals(1, $paramsValues['param2']);
    }

    /**
     * @covers DependencyBuilder::getConstructorParametersData()
     */
    public function testWithConstructorDependencies()
    {
        $fake = $this->getFakeClass();

        $rc = $this->reflectClassForTest(WithConstructorDependencies::class);

        $params = $rc->getConstructor()->getParameters();

        $givenParametersData = $fake->getConstructorParametersData($params);

        $this->assertArrayHasKey('withConstructor', $givenParametersData);
        $this->assertArrayHasKey('withoutConstructor', $givenParametersData);

        $this->assertInstanceOf(WithConstructor::class, $givenParametersData['withConstructor']);
        $this->assertInstanceOf(WithoutConstructor::class, $givenParametersData['withoutConstructor']);
    }

    public function testEnsureNoMissingParameter()
    {
        $fake = $this->getFakeClass();

        $this->expectException(MissingConstructorArgumentException::class);
        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $params = $rc->getConstructor()->getParameters();
        $paramValues = ['param1' => 1, 'param2' => 2];
        $instance = $fake->ensureNoMissingConstructorParameter($params, $paramValues, $rc);
        $this->assertNull($instance);
        $fake->ensureNoMissingConstructorParameter($params, [], $rc);
    }

    public function testCreateInstance()
    {
        $fake = $this->getFakeClass();

        $rc = $this->reflectClassForTest(Bar::class);
        $instance = $fake->createInstance($rc);
        $this->assertInstanceOf(Bar::class, $instance);
    }

    public function testCreateInstanceWithParameters()
    {
        $fake = $this->getFakeClass(null, ['bar' => new Bar()]);

        $rc = $this->reflectClassForTest(Foo::class);
        $instance = $fake->createInstanceWithParameters($rc);
        $this->assertInstanceOf(Foo::class, $instance);
    }

    public function reflectClassForTest($class)
    {
        return new ReflectionClass($class);
    }

    /**
     * Creates a ContainerContract mock for testGetSetHasParamBinds().
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function mockContainerForGetSetHasParamBinds()
    {
        $container = $this->createMock(IContainer::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $container
            ->expects($this->once())
            ->method('get')
            ->willReturn('bar');

        return $container;
    }

    /**
     * Creates a ContainerContract mock for testWithConstructorParameterBind().
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function mockContainerForTestWithConstructorParameterBind()
    {
        $containerMock = $this->createMock(IContainer::class);
        $containerMock
            ->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $containerMock
            ->expects($this->any())
            ->method('get')
            ->willReturn(new WithConstructor());
        $containerMock
            ->expects($this->any())
            ->method('get')
            ->willReturn(new WithoutConstructor());

        return $containerMock;
    }

    public function getFakeClass(IContainer $container = null, $constructorData = null)
    {
        $containerMock = $this->createMock(IContainer::class);

        $containerMock
            ->expects($this->any())
            ->method('canAutoInject')
            ->willReturn(true);


        $instance = new DependencyBuilder($container ?? $containerMock, $constructorData);

        return new Fake($instance);
    }
}
