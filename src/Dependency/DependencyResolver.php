<?php

namespace Unity\Component\Container\Dependency;

use Unity\Component\Container\Contracts\Dependency\IDependencyFactory;
use Unity\Component\Container\Contracts\Dependency\IDependencyResolver;
use Unity\Component\Container\Contracts\Factories\IBindResolverFactory;
use Unity\Component\Container\Contracts\IContainer;

/**
 * Class DependencyResolver.
 *
 * Represents and resolves a dependency.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolver implements IDependencyResolver
{
    protected $entry;
    protected $singleton;
    protected $binds = [];
    protected $arguments = [];
    protected $protectEntry = false;

    protected $dependencyFactory;
    protected $bindResolverFactory;
    protected $container;

    public function __construct(
                          $entry,
        IDependencyFactory $dependencyFactory,
        IBindResolverFactory $bindResolverFactory,
        IContainer        $container
        ) {
        $this->entry = $entry;
        $this->dependencyFactory = $dependencyFactory;
        $this->bindResolverFactory = $bindResolverFactory;
        $this->container = $container;
    }

    /**
     * Resolves the dependency.
     *
     * @return mixed
     */
    public function resolve()
    {
        return $this->getSingleton();
    }

    /**
     * Resolves and returns a new dependency on every call.
     *
     * @param array $arguments Parameters that will be used
     *                         to construct the dependency.
     *
     *                         If a parameter already has a value given using the `give()`
     *                         method, that parameters will be override by the parameter
     *                         on the $parameters
     *
     * @return mixed
     */
    public function make($arguments = [])
    {
        $entry = $this->getEntry();
        $binds = $this->getBinds();

        if (!$this->protectEntry) {
            /****************************************************
             * This will merge `DependencyResolver::$arguments` *
             * with `$arguments`.                               *
             *                                                  *
             * Items on the `DependencyResolver::$arguments`    *
             * with the same key as items on `$arguments`       *
             * will be overwritten.                             *
             ****************************************************/
            if (!empty($arguments)) {
                $arguments = array_merge($this->getArguments(), $arguments);
            }

            /////////////////////////////////////////////////////////////////////
            // If our entry is a callable, lets call it and return the output. //
            /////////////////////////////////////////////////////////////////////
            if (is_callable($entry)) {
                return call_user_func($entry, $this->container);
            }

            //////////////////////////////////////////////////////////////////////////
            // If our entry is a string and is an existing class, let's make it. :) //
            //////////////////////////////////////////////////////////////////////////
            if (is_string($entry) && class_exists($entry)) {
                return $this->dependencyFactory->make($entry, $arguments, $binds);
            }
        }

        /****************************************************************************
         * If we're here, that means our entry isn't a class, or either a callable. *
         *                                                                          *
         * In this case, we return the entry data.                                  *
         ****************************************************************************/
        return $entry;
    }

    /**
     * Prevents the entry from being resolved
     * causing its immediately return.
     *
     * @param bool $enabled
     *
     * @return DependencyResolver
     */
    public function protect($enabled = true)
    {
        $this->protectEntry = $enabled;

        return $this;
    }

    /**
     * Constructor arguments.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function give(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Gets the given arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $class
     * @param mixed  $callback
     *
     * Binds a callback return value to a class.
     *
     * Every time a class needs an argument of type `$class`,
     * the `$callback` will be invoked, and the return value will be injected.
     *
     * Different of the register method, this will not throw an exception
     * if you register a bind with the same key twice, instead, it will
     * replace the old bind with this new one.
     *
     * @return static
     */
    public function bind($class, $callback)
    {
        $this->binds[$class] = $this->bindResolverFactory->make($callback, $this->container);

        return $this;
    }

    /**
     * Gets all registered binds.
     *
     * @return array
     */
    protected function getBinds()
    {
        return $this->binds;
    }

    /**
     * Gets the resolver entry.
     *
     * @return mixed
     */
    protected function getEntry()
    {
        return $this->entry;
    }

    /**
     * Resolves and returns a dependency on the first call.
     * Only returns the resolved dependency on subsequent calls.
     *
     * @return mixed
     */
    protected function getSingleton()
    {
        if ($this->hasSingleton()) {
            return $this->singleton;
        }

        $instance = $this->make();

        $this->setSingleton($instance);

        return $instance;
    }

    /**
     * Sets the singleton dependency.
     *
     * @param $dependency
     */
    protected function setSingleton($dependency)
    {
        $this->singleton = $dependency;
    }

    /**
     * Checks if the resolver has a singleton instance.
     *
     * @return bool
     */
    protected function hasSingleton()
    {
        return !is_null($this->singleton);
    }
}
