<?php

use Helpers\Bar;
use Helpers\Foo;
use Helpers\WithConstructor;
use Helpers\WithConstructorDependencies;
use Helpers\WithConstructorParameterBind;
use Helpers\WithConstructorParameters;
use Helpers\WithoutConstructor;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IUnityContainer;
use Unity\Component\Container\Dependency\DependencyBuilder;
use Unity\Component\Container\Exceptions\MissingConstructorArgumentException;

class DependencyBuilderTest extends TestCase
{
    public function testGetHasParams()
    {
        $instanceBuilder = $this->getDependencyBuilder();

        $this->assertFalse($instanceBuilder->hasParam('name'));

        $instanceBuilder = $this->getDependencyBuilder(null, ['name' => 'Lorem Ipsum']);

        $this->assertTrue($instanceBuilder->hasParam('name'));
        $name = $instanceBuilder->getParam('name');

        $this->assertEquals('Lorem Ipsum', $name);

        $params = $instanceBuilder->getParams();
        $this->assertTrue(is_array($params));
        $this->assertNotEmpty($params);
    }

    public function testGetContainer()
    {
        $containerMock = $this->createMock(IUnityContainer::class);

        $instanceBuilder = $this->getDependencyBuilder($containerMock);

        $container = $instanceBuilder->getContainer();

        $this->assertInstanceOf(IUnityContainer::class, $container);
    }

    public function testGetHasParamBinds()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $this->assertFalse($dependencyBuilder->hasBind('foo'));

        $containerMock = $this->createMock(IUnityContainer::class);

        $containerMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $containerMock
            ->expects($this->once())
            ->method('get')
            ->willReturn('bar');

        $dependencyBuilder = $this->getDependencyBuilder($containerMock, null, ['foo' => 'bar']);

        $this->assertTrue($dependencyBuilder->hasBind('foo'));
        $bind = $dependencyBuilder->getBind('foo');

        $this->assertEquals('bar', $bind);
        $binds = $dependencyBuilder->getBinds();

        $this->assertInternalType('array', $binds);
        $this->assertNotEmpty($binds);
    }

    public function testReflectClass()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $dependencyBuilder->reflectClass(Bar::class);
        $this->assertInstanceOf(ReflectionClass::class, $rc);
    }

    public function testHasConstructor()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $this->reflectClassForTest(WithoutConstructor::class);
        $this->assertFalse($dependencyBuilder->hasConstructor($rc));
        $rc = $this->reflectClassForTest(WithConstructor::class);
        $this->assertTrue($dependencyBuilder->hasConstructor($rc));
    }

    public function testHasParametersOnConstructor()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $this->assertEquals(true, $dependencyBuilder->hasParametersOnConstructor($rc));
        $rc = $this->reflectClassForTest(WithConstructor::class);
        $this->assertEquals(false, $dependencyBuilder->hasParametersOnConstructor($rc));
    }

    public function testGetParametersRequiredByTheConstructor()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $this->reflectClassForTest(WithConstructorDependencies::class);
        $params = $dependencyBuilder->getParametersRequiredToConstructReflectedClass($rc);
        $this->assertTrue(is_array($params));
        $this->assertCount(2, $params);
        $this->assertInstanceOf(ReflectionParameter::class, $params[0]);
        $this->assertInstanceOf(ReflectionParameter::class, $params[1]);
    }

    public function testGetConstructorParametersValuesWithoutGivenParametersValues()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $params = $rc->getConstructor()->getParameters();
        $paramsValues = $dependencyBuilder->getGivenConstructorParametersData($params);
        $this->assertInternalType('array', $paramsValues);
        $this->assertEmpty($paramsValues);
    }

    public function testGetConstructorParametersValuesWithProvidedParametersValues()
    {
        $dependencyBuilder = $this->getDependencyBuilder(null, [
            'param1' => 1,
            'param2' => 1,
        ]);

        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $params = $rc->getConstructor()->getParameters();
        $paramsValues = $dependencyBuilder->getGivenConstructorParametersData($params);

        $this->assertTrue(is_array($paramsValues));
        $this->assertCount(2, $paramsValues);
        $this->assertEquals(1, $paramsValues['param1']);
        $this->assertEquals(1, $paramsValues['param2']);
    }

    public function testWithConstructorDependencies()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $this->reflectClassForTest(WithConstructorDependencies::class);

        $params = $rc->getConstructor()->getParameters();

        $givenParametersData = $dependencyBuilder->getGivenConstructorParametersData($params);

        $this->assertArrayHasKey('withConstructor', $givenParametersData);
        $this->assertArrayHasKey('withoutConstructor', $givenParametersData);

        $this->assertInstanceOf(WithConstructor::class, $givenParametersData['withConstructor']);
        $this->assertInstanceOf(WithoutConstructor::class, $givenParametersData['withoutConstructor']);
    }

    public function testWithConstructorParameterBind()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $this->reflectClassForTest(WithConstructorParameterBind::class);
        $params = $rc->getConstructor()->getParameters();
        $givenParametersData = $dependencyBuilder->getGivenConstructorParametersData($params);

        $this->assertArrayHasKey('withConstructor', $givenParametersData);
        $this->assertArrayHasKey('withoutConstructor', $givenParametersData);

        $this->assertInstanceOf(WithConstructor::class, $givenParametersData['withConstructor']);
        $this->assertInstanceOf(WithoutConstructor::class, $givenParametersData['withoutConstructor']);
    }

    public function testEnsureNoMissingParameter()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $this->expectException(MissingConstructorArgumentException::class);
        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $params = $rc->getConstructor()->getParameters();
        $paramValues = ['param1' => 1, 'param2' => 2];
        $instance = $dependencyBuilder->ensureNoMissingConstructorParameter($params, $paramValues, $rc);
        $this->assertNull($instance);
        $dependencyBuilder->ensureNoMissingConstructorParameter($params, [], $rc);
    }

    public function testCreateInstance()
    {
        $dependencyBuilder = $this->getDependencyBuilder();

        $rc = $this->reflectClassForTest(Bar::class);
        $instance = $dependencyBuilder->createInstance($rc);
        $this->assertInstanceOf(Bar::class, $instance);
    }

    public function testCreateInstanceWithParameters()
    {
        $dependencyBuilder = $this->getDependencyBuilder(null, ['bar' => new Bar()]);

        $rc = $this->reflectClassForTest(Foo::class);
        $instance = $dependencyBuilder->createInstanceWithParameters($rc);
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
        $container = $this->createMock(IUnityContainer::class);
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
        $containerMock = $this->createMock(IUnityContainer::class);
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

    public function getDependencyBuilder(IUnityContainer $container = null, $params = null, $binds = null)
    {
        $containerMock = $this->createMock(IUnityContainer::class);

        $containerMock
            ->expects($this->any())
            ->method('canAutowiring')
            ->willReturn(true);

        return new DependencyBuilder($container ?? $containerMock, $params, $binds);
    }
}
