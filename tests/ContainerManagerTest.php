<?php

use e200\MakeAccessible\Make;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\ContainerManager;
use Unity\Contracts\Container\Dependency\IDependencyFactory;
use Unity\Contracts\Container\Factories\IBindResolverFactory;
use Unity\Contracts\Container\Factories\IDependencyResolverFactory;
use Unity\Contracts\Container\IContainer;

class ContainerManagerTest extends TestCase
{
    public function testAutoResolver()
    {
        $acb = $this->getAccessibleInstance();
        $cb = $acb->getInstance();

        $this->assertTrue($acb->autoResolve);

        $cb->autoResolve(false);
        $this->assertFalse($acb->autoResolve);
        $cb->autoResolve(true);
        $this->assertTrue($acb->autoResolve);
    }

    public function testCanUseAnnotations()
    {
        $acb = $this->getAccessibleInstance();
        $cb = $acb->getInstance();

        $this->assertFalse($acb->useAnnotations);

        $cb->canUseAnnotations(true);
        $this->assertTrue($acb->useAnnotations);
        $cb->canUseAnnotations(false);
        $this->assertFalse($acb->useAnnotations);
    }

    public function testGetDependencyFactory()
    {
        $accessibleInstance = $this->getAccessibleInstance();

        $this->assertInstanceOf(
            IDependencyFactory::class,
            $accessibleInstance->getDependencyFactory()
        );
    }

    public function testGetDependencyResolverFactory()
    {
        $accessibleInstance = $this->getAccessibleInstance();

        $this->assertInstanceOf(
            IDependencyResolverFactory::class,
            $accessibleInstance->getDependencyResolverFactory()
        );
    }

    public function testGetBindResolverFactory()
    {
        $accessibleInstance = $this->getAccessibleInstance();

        $this->assertInstanceOf(
            IBindResolverFactory::class,
            $accessibleInstance->getBindResolverFactory()
        );
    }

    public function testBuild()
    {
        $instance = $this->getInstance();

        $this->assertInstanceOf(IContainer::class, $instance->build());
    }

    public function getInstance()
    {
        return new ContainerManager();
    }

    public function getAccessibleInstance()
    {
        return Make::accessible($this->getInstance());
    }
}
