<?php

use Helpers\Complex;
use Helpers\Foo;
use Helpers\Bar;
use Helpers\WithConstructorDependencies;
use Helpers\WithConstructor;
use Helpers\WithConstructorParameterBind;
use Helpers\WithConstructorParameters;
use Helpers\WithoutConstructor;
use PHPUnit\Framework\TestCase;
use Unity\Component\IoC\ContainerContract;
use Unity\Component\IoC\Exceptions\MissingConstructorArgumentException;
use Unity\Component\IoC\InstanceBuilder;

class InstanceBuilderTest extends TestCase
{
    private $instanceBuilder;

    function setUp()
    {
        parent::setUp();

        $this->instanceBuilder = new InstanceBuilder;
    }

    /**
     * We check if a parameter exists,
     * since we don't provided any parameter yet,
     * `hasParam($param)` should return `false`.
     *
     * Once we `setParams(array $params)`, `hasParam($param)`
     * should return `true`
     *
     * Since `hasParam($param)` returns `true`, `getParam($param)`
     * should only return the value referenced by the `$param`
     *
     * If everything is right, `getParams()` should return all
     * params wtithout any problem
     */
    function testGetSetHasParams()
    {
        $this->assertFalse($this->instanceBuilder->hasParam('name'));
        $this->instanceBuilder->setParams(['name' => 'Lorem Ipsum']);
        $this->assertTrue($this->instanceBuilder->hasParam('name'));

        $name = $this->instanceBuilder->getParam('name');
        $this->assertEquals('Lorem Ipsum', $name);

        $params = $this->instanceBuilder->getParams();

        $this->assertTrue(is_array($params));
        $this->assertNotEmpty($params);
    }

    /**
     * We need to mock a `ContainerContract` instance and set it,
     * so we can test the `setContainer(ContainerContract $container)`
     *
     * Once we set the `Container` mock, lets test the `getContainer()`,
     * if the `Container` mock was set successfully, the `getContainer()`
     * should return the mocked Container instance without any trouble
     */
    function testSetGetContainer()
    {
        $container = $this->createMock(ContainerContract::class);

        $this->instanceBuilder->setContainer($container);

        $container = $this->instanceBuilder->getContainer();

        $this->assertInstanceOf(ContainerContract::class, $container);
    }

    /**
     * We need to mock a `ContainerContract` instance and
     * provide the method `has($bind)` of the `Container`.
     * The `hasBind($bind)` first checks if a bind with
     * the given name was provided, if yes, its
     * checks if the requested bind is registered on the
     * `ContainerContract`,after it, we `setContainer($container)`
     *
     * We're ready to test
     *
     * Since no bind was provided, `hasBind($bind)`
     * should return `false`
     *
     * After it, we set a bind
     *
     * Since we have a bind, `hasBind($bind)` should return `true`
     *
     * Ok... We have a bind... Lets get it!!!
     *
     * Ok, now, lets get all binds. Of course we just have one
     */
    function testGetSetHasParamBinds()
    {
        $container = $this->mockContainerForGetSetHasParamBinds();

        $this->instanceBuilder->setContainer($container);

        $this->assertFalse($this->instanceBuilder->hasBind('foo'));
        $this->instanceBuilder->setBinds(['foo' => 'bar']);
        $this->assertTrue($this->instanceBuilder->hasBind('foo'));

        $bind = $this->instanceBuilder->getBind('foo');
        $this->assertEquals('bar', $bind);

        $binds = $this->instanceBuilder->getBinds();

        $this->assertTrue(is_array($binds));
        $this->assertNotEmpty($binds);
    }

    /** Should return a `ReflectionClass` instance */
    function testReflectClass()
    {
        $rc = $this->instanceBuilder->reflectClass(Bar::class);

        $this->assertInstanceOf(ReflectionClass::class, $rc);
    }

    /**
     * `canAutowiring()` should return `true` by default
     *
     * `canAutowiring()` should return `false` if since
     * `enableAutowiring(false)`
     *
     * `canAutowiring()` should return `true` if
     * `enableAutowiring(true)`
     */
    function testAutowiring()
    {
        $this->assertEquals(true, $this->instanceBuilder->canAutowiring());

        $this->instanceBuilder->enableAutowiring(false);
        $this->assertEquals(false, $this->instanceBuilder->canAutowiring());

        $this->instanceBuilder->enableAutowiring(true);
        $this->assertEquals(true, $this->instanceBuilder->canAutowiring());
    }

    /**
     * `hasConstructor()` should return `true` with classes
     * that has a constructor
     */
    function testHasConstructor()
    {
        $rc = $this->reflectClassForTest(WithoutConstructor::class);
        $this->assertFalse($this->instanceBuilder->hasConstructor($rc));

        $rc = $this->reflectClassForTest(WithConstructor::class);
        $this->assertTrue($this->instanceBuilder->hasConstructor($rc));
    }

    /**
     * `hasParametersOnConstructor()` should return `true` with
     * classes that has parameters on constructor
     */
    function testHasParametersOnConstructor()
    {
        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $this->assertEquals(true, $this->instanceBuilder->hasParametersOnConstructor($rc));

        $rc = $this->reflectClassForTest(WithConstructor::class);
        $this->assertEquals(false, $this->instanceBuilder->hasParametersOnConstructor($rc));
    }

    /**
     * `getParametersNeededByTheConstructor()` should return
     * an `array` containing all constructor parameters, the
     * exactly number of parameters required and all parameters
     * should be of type ReflectionParameter
     */
    function testGetParametersRequiredByTheConstructor()
    {
        $rc = $this->reflectClassForTest(WithConstructorDependencies::class);

        $params = $this->instanceBuilder->getParametersRequiredByTheConstructor($rc);

        $this->assertTrue(is_array($params));
        $this->assertCount(2, $params);
        $this->assertInstanceOf(ReflectionParameter::class, $params[0]);
        $this->assertInstanceOf(ReflectionParameter::class, $params[1]);
    }

    /**
     * `getConstructorParametersValues()` should return an empty
     * `array` if no parameters provided using `setParams(array $params)`
     */
    function testGetConstructorParametersValuesWithoutProvideParametersValues()
    {
        $rc = $this->reflectClassForTest(WithConstructorParameters::class);

        $params = $rc->getConstructor()->getParameters();
        $paramsValues = $this->instanceBuilder->getConstructorParametersValues($params);

        $this->assertTrue(is_array($paramsValues));
        $this->isEmpty($paramsValues);
    }

    /**
     * `getConstructorParametersValues()` should return an
     * `array` of values for each constructor required parameter
     * if parameters provided with `setParams(array $params)`
     */
    function testGetConstructorParametersValuesWithProvidedParametersValues()
    {
        $this->instanceBuilder->setParams([
            'param1' => 1,
            'param2' => 1
        ]);

        $rc = $this->reflectClassForTest(WithConstructorParameters::class);

        $params = $rc->getConstructor()->getParameters();
        $paramsValues = $this->instanceBuilder->getConstructorParametersValues($params);

        $this->assertTrue(is_array($paramsValues));
        $this->assertCount(2, $paramsValues);
        $this->assertEquals(1, $paramsValues['param1']);
        $this->assertEquals(1, $paramsValues['param2']);
    }

    /**
     * `getConstructorParametersValues()` should return an `array` containing
     * all class dependencies to construct the provided class
     */
    function testWithConstructorDependencies()
    {
        $rc = $this->reflectClassForTest(WithConstructorDependencies::class);

        $params = $rc->getConstructor()->getParameters();
        $paramsValues = $this->instanceBuilder->getConstructorParametersValues($params);

        $this->assertInstanceOf(WithConstructor::class, $paramsValues['withConstructor']);
        $this->assertInstanceOf(WithoutConstructor::class, $paramsValues['withoutConstructor']);
    }

    /**
     * `getConstructorParametersValues()` should return an `array` containing
     * all dependencies required to construct the provided class
     *
     * These dependencies are singletons shared across all
     * classes that binds to it, a bind is provided by a `ContainerContract`
     * instance, that's why we're using double test (Mock)
     */
    function testWithConstructorParameterBind()
    {
        $containerMock = $this->mockContainerForTestWithConstructorParameterBind();

        $this->instanceBuilder->setContainer($containerMock);

        $rc = $this->reflectClassForTest(WithConstructorParameterBind::class);

        $params = $rc->getConstructor()->getParameters();
        $paramsValues = $this->instanceBuilder->getConstructorParametersValues($params);

        $this->assertInstanceOf(WithConstructor::class, $paramsValues['withConstructor']);
        $this->assertInstanceOf(WithoutConstructor::class, $paramsValues['withoutConstructor']);
    }

    /**
     * `ensureNoMissingConstructorParameter()` should return
     * nothing at the first test 'cause we provide the
     * parameters required to build the given class, that's why we
     * ensure the output is `null`
     *
     * `ensureNoMissingConstructorParameter()` should throw an exception
     * on the second test 'cause we did'nt provide the parameters required
     * to build the given class
     */
    function testEnsureNoMissingParameter()
    {
        $this->expectException(MissingConstructorArgumentException::class);

        $rc = $this->reflectClassForTest(WithConstructorParameters::class);
        $params = $rc->getConstructor()->getParameters();

        $paramValues = ['param1' => 1, 'param2' => 2];

        $instance = $this->instanceBuilder->ensureNoMissingConstructorParameter($params, $paramValues, $rc);
        $this->assertNull($instance);

        $this->instanceBuilder->ensureNoMissingConstructorParameter($params, [], $rc);
    }

    /**
     * `createInstance()` should return an instance of the provided class
     *
     * Helpers\Bar has'nt a constructor
     */
    function testCreateInstance()
    {
        $rc = $this->reflectClassForTest(Bar::class);

        $instance = $this->instanceBuilder->createInstance($rc);

        $this->assertInstanceOf(Bar::class, $instance);
    }

    /**
     * `createInstanceWithParameters()` should return an instance of the provided class
     *
     * Helpers\Foo has a constructor with a dependency,
     * all dependencies should be resolved and injected
     */
    function testCreateInstanceWithParameters()
    {
        $rc = $this->reflectClassForTest(Foo::class);

        $instance = $this->instanceBuilder->createInstanceWithParameters($rc);

        $this->assertInstanceOf(Foo::class, $instance);
    }

    /**
     * "Reflect class for test"
     *
     * @param $class
     * @return ReflectionClass
     */
    function reflectClassForTest($class)
    {
        return new ReflectionClass($class);
    }

    /**
     * Creates a ContainerContract mock for testGetSetHasParamBinds()
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    function mockContainerForGetSetHasParamBinds()
    {
        $container = $this->createMock(ContainerContract::class);

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
     * Creates a ContainerContract mock for testWithConstructorParameterBind()
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     */
    function mockContainerForTestWithConstructorParameterBind()
    {
        $containerMock = $this->createMock(ContainerContract::class);

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
            ->willReturn(new WithoutConstructor);

        return $containerMock;
    }
}
