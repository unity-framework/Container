<?php

use e200\MakeAccessible\Make;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\ContainerManager;
use Unity\Component\Container\Contracts\Dependency\IDependencyFactory;
use Unity\Component\Container\Contracts\Factories\IBindResolverFactory;
use Unity\Component\Container\Contracts\Factories\IDependencyResolverFactory;
use Unity\Component\Container\Contracts\IContainer;

class ContainerManagerTest extends TestCase
{
    public function testAutoResolver()
    {
        $instance = $this->getInstance();
        $accessibleInstance = $this->getAccessibleInstance($instance);

        /**
         * `ContainerManager::autoResolve` must
         * must be true by default
         */
        $this->assertTrue($accessibleInstance->autoResolve);

        $instance->autoResolve(false);
        $this->assertFalse($accessibleInstance->autoResolve);
        $instance->autoResolve(true);
        $this->assertTrue($accessibleInstance->autoResolve);
    }

    public function testCanUseAnnotations()
    {
        $instance = $this->getInstance();
        $accessibleInstance = $this->getAccessibleInstance($instance);

        $this->assertFalse($accessibleInstance->useAnnotations);

        $instance->canUseAnnotations(true);
        $this->assertTrue($accessibleInstance->useAnnotations);
        $instance->canUseAnnotations(false);
        $this->assertFalse($accessibleInstance->useAnnotations);
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

    public function getAccessibleInstance($instance = null)
    {
        if (!$instance) {
            $instance = $this->getInstance();
        }

        return Make::accessible($instance);
    }
}
