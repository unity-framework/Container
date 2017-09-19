<?php

namespace Unity\Component\Container\Dependency;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Unity\Component\Container\Contracts\IUnityContainer;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Exceptions\ContainerException;

/**
 * Class DependencyResolver.
 *
 * @package Unity\Component\Container\Dependency
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyResolver implements IDependencyResolver
{
    protected $id;
    protected $entry;
    protected $params = [];
    protected $binds = [];
    protected $singleton;
    protected $container;

    function __construct(string $id, $entry, IUnityContainer $container)
    {
        $this->id = $id;
        $this->entry = $entry;
        $this->container = $container;
    }

    /**
     * Gets the resolver id.
     *
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Gets resolver entry.
     *
     * @return mixed
     */
    function getEntry()
    {
        return $this->entry;
    }

    /**
     * Checks if resolver has a singleton instance.
     *
     * @return bool
     */
    function hasSingleton()
    {
        return !is_null($this->singleton);
    }

    /**
     * Resolves and returns dependency on the first call,
     * and returns only the resolved dependency on subsequent calls.
     *
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    function getSingleton()
    {
        if($this->hasSingleton())
            return $this->singleton;

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
    function setSingleton($dependency)
    {
        return $this->singleton = $dependency;
    }

    /**
     * Resolves resolver dependency.
     *
     * @return mixed
     */
    function resolve()
    {
        return $this->getSingleton();
    }

    /**
     * Resolves and returns a new dependency on every call.
     *
     * @param array $params
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    function make($params = [])
    {
        $instance = null;
        $entry = $this->getEntry();

        if (!empty($params)) {
            $params = array_merge($params, $this->params);
        }

        if(is_string($entry) && class_exists($entry)) {
            try {
                return (new DependencyBuilder(
                    $this->container,
                    $params,
                    $this->binds
                ))->build($entry);
            } catch (Exception $ex) {
                throw new ContainerException("An error occurs while trying to build \" {$this->id} \".\nError: " . $ex->getMessage(), $ex->getCode());
            }
        }

        if (is_callable($entry)) {
            return call_user_func($entry, $this->container);
        }

        return $entry;
    }

    /**
     * Parameters to be given to the constructor on build time.
     *
     * @param array $params
     *
     * @return $this
     */
    function give(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Gets the given parameters
     *
     * @return array
     */
    function getParams()
    {
        return $this->params;
    }

    /**
     * Binds others dependencies on container to this dependency.
     *
     * @param array $to
     *
     * @return $this
     */
    function bind(array $to)
    {
        $this->binds = $to;

        return $this;
    }

    /**
     * Gets the given binds
     *
     * @return array
     */
    function getBinds()
    {
        return $this->binds;
    }
}
