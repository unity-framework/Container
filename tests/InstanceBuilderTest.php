<?php

use PHPUnit\Framework\TestCase;
use Unity\Component\IoC\InstanceBuilder;
use Test\Helpers\Foo;
use Test\Helpers\Bar;

class InstanceBuilderTest extends TestCase
{
    function testAutowiring()
    {
        $ib = new InstanceBuilderTester;

        $this->assertEquals(true, $ib->canAutowiring());

        InstanceBuilderTester::autowiring(false);

        $this->assertEquals(false, $ib->canAutowiring());
    }

    function testGetReflectionClass()
    {
        $ib = new InstanceBuilderTester;

        $this->assertInstanceOf(\ReflectionClass::class, $ib->getReflectionClass(Bar::class));
    }

    function testHasParameters()
    {
        $db = new InstanceBuilderTester;

        $refClass = new ReflectionClass(Foo::class);

        $this->assertEquals(true, $db->hasParameters($refClass));
    }

    function testHasNotParameters()
    {
        $db = new InstanceBuilderTester;

        $refClass = new ReflectionClass(Bar::class);

        $this->assertEquals(false, $db->hasParameters($refClass));
    }

    function testGetParametersType()
    {
        $db = new InstanceBuilderTester;

        $refClass = new ReflectionClass(Foo::class);

        $this->assertEquals([
            Bar::class
        ], $db->getParametersType($refClass));

        $refClass = new ReflectionClass(Bar::class);

        $this->assertEquals([], $db->getParametersType($refClass));
    }

    function testHasDependencies()
    {
        $ib = new InstanceBuilderTester;

        $refClass = $ib->getReflectionClass(Foo::class);

        /**
         * This method set the $hasDependency,
         * so, we need to run it first to check
         * if the class has or not dependencies
         */
        $ib->getDependencies($refClass);

        $this->assertEquals(true, $ib->hasDependencies());
    }

    function testHasNotDependencies()
    {
        $ib = new InstanceBuilderTester;

        $refClass = new ReflectionClass(Bar::class);

        $ib->getDependencies($refClass);

        $this->assertEquals(false, $ib->hasDependencies());
    }

    function testBuild()
    {
        $ib = new InstanceBuilderTester;

        $instance = $ib->build(Foo::class);

        $this->assertInstanceOf(Foo::class, $instance);
    }
}

class InstanceBuilderTester extends InstanceBuilder
{
    function __construct() {}
    function __clone()
    {
    }
}