<?php

namespace Unity\Component\Container\Dependency;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Contracts\IResolver;
use Unity\Component\Container\Exceptions\ContainerException;

/**
 * Class DependencyResolver.
 *
 * Represents and resolves a dependency.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolver implements IResolver
{
    protected $id;
    protected $entry;
    protected $container;
    protected $singleton;
    protected $constructorData = [];

    /**
     * DependencyResolver constructor.
     *
     * @param string     $id
     * @param mixed      $entry
     * @param IContainer $container
     */
    public function __construct(string $id, $entry, IContainer $container)
    {
        $this->id        = $id;
        $this->entry     = $entry;
        $this->container = $container;
    }

    /**
     * Gets the resolver id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the resolver entry.
     *
     * @return mixed
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Resolves and returns a dependency on the first call.
     * Only returns the resolved dependency on subsequent calls.
     *
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function getSingleton()
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
     *
     * @return mixed
     */
    public function setSingleton($dependency)
    {
        return $this->singleton = $dependency;
    }

    /**
     * Checks if the resolver has a singleton instance.
     *
     * @return bool
     */
    public function hasSingleton()
    {
        return !is_null($this->singleton);
    }

    /**
     * Resolves the resolver dependency.
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
     * @param array $constructorData Parameters that will be used
     * to construct the dependency.
     * If a parameter already has a value given using the `give()`
     * method, that parameters will be override by the parameter
     * on the $parameters
     *
     * @throws ContainerException
     * @throws Exception
     *
     * @return mixed
     */
    public function make($constructorData = null)
    {
        $entry = $this->getEntry();

        /*
         * This will merge user givens parameters using the `give()` method
         * with $parameters.
         *
         * Elements on the first array with the same key on the second array
         * will be override with the second array data.
         */
        if (!is_null($constructorData)) {
            $constructorData = array_merge($this->constructorData, $constructorData);
        }

        /*
         * If our entry is a string and is an existing class,
         * let's build it :)
         */
        if (is_string($entry) && class_exists($entry)) {
            try {
                return (new DependencyBuilder(
                    $this->container,
                    $constructorData
                ))->build($entry);
            } catch (Exception $ex) {
                if($ex->getCode() == 0200)
                    throw $ex;

                throw new ContainerException("An error occurs while trying to build \" {$this->id} \".\nError: ".$ex->getMessage(), $ex->getCode());
            }
        }

        /*
         * If our entry is a callable, lets call it and return the output.
         */
        if (is_callable($entry)) {
            return call_user_func($entry, $this->container);
        }


        /*
         * If we're here, that means our entry it's
         * not a class, or either a callable.
         *
         * In this case, we return the entry data.
         */
        return $entry;
    }

    /**
     * Parameters that will be given to the constructor on build time.
     *
     * @param array $params
     *
     * @return $this
     */
    public function give(array $params)
    {
        $this->constructorData = $params;

        return $this;
    }

    /**
     * Gets the given parameters.
     *
     * @return array
     */
    public function getGivenParams()
    {
        return $this->constructorData;
    }

    /**
     * Binds others dependencies on container to this dependency.
     *
     * @param array $to
     *
     * @return $this
     */
    public function bind(array $to)
    {
        $this->binds = $to;

        return $this;
    }

    /**
     * Gets the given binds.
     *
     * @return array
     */
    public function getBinds()
    {
        return $this->binds;
    }
}
