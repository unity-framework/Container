<?php

use Test\Helpers\Foo;
use Test\Helpers\Bar;

use PHPUnit\Framework\TestCase;
use Unity\Component\IoC\Resolver;

class ResolverTest extends TestCase
{
    private $resolver;

    protected function setUp()
    {
        parent::setUp();

        $this->resolver = new Resolver('foo', Foo::class);
    }

    /**
     * Should return 'foo'
     */
    function testGetName()
    {
        $name = $this->resolver->getName();
        $this->assertEquals('foo', $name);
    }

    /**
     * Should return 'Test\Helpers\Foo'
     */
    function testGetEntry()
    {
        $entry = $this->resolver->getEntry();
        $this->assertEquals(Foo::class, $entry);
    }

    /**
     * Should return false, there's isn't any
     * instance set yet
     */
    function testHasNotSingleton()
    {
        $hasInstance = $this->resolver->hasSingleton();
        $this->assertEquals(false, $hasInstance);
    }

    /**
     * Should return a non empty value
     * Should return an instance of 'Test\Helpers\Foo'
     * Should return the same instance ever
     */
    function testGetSingleton()
    {
        $instance = $this->resolver->getSingleton();
        $this->assertNotEmpty($instance);
        $this->assertInstanceOf(Foo::class, $instance);
        $this->assertSame($instance, $this->resolver->getSingleton());
    }

    /**
     * Should return the same instance
     * Otherwise the setInstance() is not working
     * as expected
     */
    function testSetSingleton()
    {
        $resolver = new Resolver('bar', null);

        $bar = new Bar;

        $resolver->setSingleton($bar);
        $this->assertSame($bar, $resolver->getSingleton());
    }

    /**
     * Should return true, there's an instance
     */
    function testHasSingleton()
    {
        /**
         * It's necessary to run this code first
         * so we can create an instance
         */
        $this->resolver->getSingleton();

        $hasInstance = $this->resolver->hasSingleton();
        $this->assertEquals(true, $hasInstance);
    }

    /**
     * Should return a new instance of 'Test\Helper\bar'
     */
    function testWithCallback()
    {
        $resolver = new Resolver('bar', function (){
           return new Bar;
        });

        $instance = $resolver->getSingleton();
        $this->assertInstanceOf(Bar::class, $instance);
    }

    /**
     * Should return a new instance of 'Test\Helper\bar'
     */
    function testWithClassName()
    {
        $resolver = new Resolver('bar', Bar::class);

        $instance = $resolver->getSingleton();
        $this->assertInstanceOf(Bar::class, $instance);
    }

    /**
     * Should return the value passed since it's not a callback
     * or a existent class
     */
    function testWithAnyValue()
    {
        $resolver = new Resolver('bar', (5 + 5));

        $instance = $resolver->getSingleton();
        $this->assertEquals(10, $instance);
    }

    /**
     * Should not return the exactly same instance
     */
    function testMake()
    {
        $resolver = new Resolver('foo', Foo::class);

        $a = $resolver->make();
        $b = $resolver->make();

        $this->assertNotSame($a, $b);
    }
}
