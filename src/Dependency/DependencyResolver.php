<?php

namespace Unity\Component\Container\Dependency;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Exceptions\ContainerException;

/**
 * Class DependencyResolver.
 *
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

    public function __construct(string $id, $entry, IContainer $container)
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets resolver entry.
     *
     * @return mixed
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Checks if resolver has a singleton instance.
     *
     * @return bool
     */
    public function hasSingleton()
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
     * Resolves resolver dependency.
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
     * @param array $params
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    public function make($params = [])
    {
        $instance = null;
        $entry = $this->getEntry();

        if (!empty($params)) {
            $params = array_merge($params, $this->params);
        }

        if (is_string($entry) && class_exists($entry)) {
            try {
                return (new DependencyBuilder(
                    $this->container,
                    $params,
                    $this->binds
                ))->build($entry);
            } catch (Exception $ex) {
                throw new ContainerException("An error occurs while trying to build \" {$this->id} \".\nError: ".$ex->getMessage(), $ex->getCode());
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
    public function give(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Gets the given parameters.
     *
     * @return array
     */
    public function getParams()
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
