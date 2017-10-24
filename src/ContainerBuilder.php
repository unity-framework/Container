<?php

namespace Unity\Component\Container;

use Unity\Reflector\Reflector;
use Unity\Component\Container\Dependency\DependencyFactory;
use Unity\Component\Container\Factories\BindResolverFactory;
use Unity\Component\Container\Factories\DependencyResolverFactory;

/**
 * Class ContainerBuilder.
 *
 * Container builder.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class ContainerBuilder
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

    public function build()
    {
        $dependencyFactory = new DependencyFactory(
            $this->autoResolve,
            $this->useAnnotations,
            new Reflector()
        );

        $dependencyResolverFactory = new DependencyResolverFactory();
        $bindResolverFactory = new BindResolverFactory();

        $container = new Container(
            $dependencyFactory,
            $dependencyResolverFactory,
            $bindResolverFactory
        );

        return $container;
    }
}
