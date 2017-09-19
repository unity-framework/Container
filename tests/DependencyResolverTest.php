<?php

use Helpers\Bar;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\IUnityContainer;
use Unity\Component\Container\Dependency\DependencyResolver;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolverTest extends TestCase
{
    const ID = 'testId';

    public function testGetId()
    {
        $dependencyResolver = $this->getDependencyResolver();

        $this->assertEquals(self::ID, $dependencyResolver->getId());
    }

    public function testGetEntry()
    {
        $dependencyResolver = $this->getDependencyResolver();

        $this->assertEquals(Bar::class, $dependencyResolver->getEntry());
    }

    public function testGiveGetParams()
    {
        $dependencyResolver = $this->getDependencyResolver();
        $params = ['e200', 'Eleandro'];

        $instance = $dependencyResolver->give($params);
        $this->assertSame($dependencyResolver, $instance);

        $this->assertSame($params, $dependencyResolver->getParams());
    }

    public function testBindGetBinds()
    {
        $dependencyResolver = $this->getDependencyResolver();
        $binds = ['e200', 'Eleandro'];

        $instance = $dependencyResolver->bind($binds);
        $this->assertSame($dependencyResolver, $instance);

        $this->assertSame($binds, $dependencyResolver->getBinds());
    }

    public function testMakeWithClassName()
    {
        $dependencyResolver = $this->getDependencyResolver();

        $instance1 = $dependencyResolver->make();
        $instance2 = $dependencyResolver->make();

        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testMakeWithCallback()
    {
        $dependencyResolver = $this->getDependencyResolver(function () {
            return new Bar();
        });

        $instance = $dependencyResolver->make();

        $this->assertInstanceOf(Bar::class, $instance);
    }

    public function testMakeWithValue()
    {
        $dependencyResolver = $this->getDependencyResolver('e200');

        $value = $dependencyResolver->make();

        $this->assertEquals('e200', $value);
    }

    public function testSetSingleton()
    {
        $dependencyResolver = $this->getDependencyResolver();

        $this->assertEquals('e200', $dependencyResolver->setSingleton('e200'));
    }

    public function testGetHasSingleton()
    {
        $dependencyResolver = $this->getDependencyResolver();

        $this->assertFalse($dependencyResolver->hasSingleton());

        $instance = $dependencyResolver->getSingleton();

        $this->assertInstanceOf(Bar::class, $instance);

        $this->assertTrue($dependencyResolver->hasSingleton());
    }

    public function testResolve()
    {
        $dependencyResolver = $this->getDependencyResolver();

        $instance = $dependencyResolver->resolve();

        $this->assertInstanceOf(Bar::class, $instance);
    }

    public function getDependencyResolver($entry = null)
    {
        $containerMock = $this->createMock(IUnityContainer::class);

        $containerMock
            ->expects($this->any())
            ->method('canAutowiring')
            ->willReturn(true);

        return new DependencyResolver(self::ID, $entry ?? Bar::class, $containerMock);
    }
}
