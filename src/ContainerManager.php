<?php

namespace Unity\Component\Container;

use Unity\Reflector\Reflector;
use Unity\Contracts\Container\IContainer;
use Unity\Contracts\Container\IContainerManager;
use Unity\Component\Container\Dependency\DependencyFactory;
use Unity\Component\Container\Factories\BindResolverFactory;
use Unity\Component\Container\Factories\DependencyResolverFactory;

/**
 * Class ContainerManager.
 *
 * Container manager.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class ContainerManager implements IContainerManager
{
    protected $autoResolve = true;
    protected $useAnnotations = false;

    /**
     * Enable|Disable auto depependencies resolution.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function autoResolve($enable)
    {
        $this->autoResolve = $enable;

        return $this;
    }

    /**
     * Enable|Disable dependencies resolution using annotations.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function canUseAnnotations($enable)
    {
        $this->useAnnotations = $enable;

        return $this;
    }

    /**
     * Returns an `IDependencyFactory` instance.
     * 
     * @return IDependencyFactory.
     */
    protected function getDependencyFactory()
    {
        return new DependencyFactory(
            $this->autoResolve,
            $this->useAnnotations,
            new Reflector()
        );
    }

    /**
     * Returns an `IDependencyResolverFactory` instance.
     * 
     * @return IDependencyResolverFactory.
     */
    protected function getDependencyResolverFactory()
    {
        return new DependencyResolverFactory();
    }

    /**
     * Returns an `IBindResolverFactory` instance.
     * 
     * @return IBindResolverFactory.
     */
    protected function getBindResolverFactory()
    {
        return new BindResolverFactory();
    }

    /**
     * Builds an `IContainer` instance. 
     * 
     * @return IContainer
     */
    public function build()
    {
        $dependencyFactory         = $this->getDependencyFactory();
        $dependencyResolverFactory = $this->getDependencyResolverFactory();
        $bindResolverFactory       = $this->getBindResolverFactory();

        $container = new Container(
            $dependencyFactory,
            $dependencyResolverFactory,
            $bindResolverFactory
        );

        return $container;
    }
}
