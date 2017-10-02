<?php

namespace Unity\Component\Container;

use ArrayAccess;
use Countable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Bind\BindResolver;
use Unity\Component\Container\Dependency\DependencyFactory;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;

/**
 * Class Container.
 *
 * Dependencies manager.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class Container implements IContainer
{
    protected $binds             = [];
    protected $resolvers         = [];
    protected $canAutowiring     = true;
    protected $canUseAnnotations = true;

    protected $dependencyFactory;

    /**
     * Sets the `DependencyFactory` instance.
     *
     * @param DependencyFactory $dependencyFactory
     */
    function setDependencyFactory(DependencyFactory $dependencyFactory)
    {
        $this->dependencyFactory = $dependencyFactory;
    }

    /**
     * Register a dependency resolver.
     *
     * @param string $id
     * @param mixed  $entry Content that will be used to generate the dependency.
     *
     * @return DependencyResolver
     *
     * @throws DuplicateIdException
     */
    public function register($id, $entry)
    {
        if ($this->has($id)) {
            throw new DuplicateIdException("The container already has a dependency resolver for id \"{$id}\".");
        }

        return $this->resolvers[$id] = new DependencyResolver(
            $entry,
            $this->dependencyFactory,
            $this
        );
    }

    /**
     * Unregister a resolver.
     *
     * @param string $id
     *
     * @throws NotFoundException
     *
     * @return Container
     */
    public function unregister($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("No dependency resolver was founded for id \"{$id}\" on the container.");
        }

        unset($this->resolvers[$id]);

        return $this;
    }

    /**
     * Replaces a registered resolver.
     *
     * This method does'nt replaces dependencies already resolved by the container.
     *
     * @param string $id
     * @param mixed  $entry
     *      Content that will be used to resolve the dependency.
     *
     * @return DependencyResolver
     */
    public function replace($id, $entry)
    {
        return $this->resolvers[$id] = new DependencyResolver(
            $entry,
            $this->dependencyFactory,
            $this
        );
    }

    /**
     * Resolves and returns the dependency on the first call.
     * Returns the resolved dependency on subsequent calls.
     *
     * @param string $id Dependency resolver identifier.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function get($id)
    {
        if ($this->has($id)) {
            return $this->resolvers[$id]->resolve();
        }

        throw new NotFoundException("No dependency resolver was founded for id \"{$id}\" on the container.");
    }

    /**
     * Checks if the container has a dependency resolver for the given $id.
     *
     * @param string $id Dependency resolver identifier.
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->resolvers[$id]);
    }

    /**
     * Resolves and returns the registered dependency on every call.
     *
     * @param string     $id     Dependency resolver identifier.
     * @param array|null $params
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function make($id, $params = null)
    {
        if ($this->has($id)) {
            return $this->resolvers[$id]->make($params);
        }

        throw new NotFoundException("No dependency resolver was founded for id \"{$id}\" on the container.");
    }

    /**
     * @param string $class
     * @param mixed  $callback
     *
     * Binds a concrete class to an interface.
     *
     * Every time a class needs an argument of type `$class`,
     * the `$callback` will be invoked, and the return value will be injected.
     *
     * Different of the register method, this will not throw an exception
     * if you register an bind with the same key twice, instead, it will
     * replace the old bind by this new one.
     *
     * @return static
     */
    public function bind(string $class, $callback)
    {
        $this->binds[$class] = new BindResolver($callback, $this);

        return $this;
    }

    /**
     * @param $class
     *
     * @return bool
     */
    public function isBound(string $class)
    {
        return isset($this->binds[$class]);
    }

    /**
     * @param string $class
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function getBoundValue(string $class)
    {
        if ($this->isBound($class)) {
            return $this->binds[$class]->resolve();
        }

        throw new NotFoundException("No resolver was bound to class \"{$class}\" on the container.");
    }

    /**
     * Enable|Disable autowiring.
     *
     * Tells the container if it should auto wiring.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function enableAutowiring($enable)
    {
        $this->canAutowiring = $enable;

        return $this;
    }

    /**
     * Checks if injection is enabled.
     *
     * @return bool
     */
    public function canAutowiring()
    {
        return $this->canAutowiring;
    }

    /**
     * Enable|Disable the use of annotations.
     *
     * Tells the container if it can inspect annotations
     * searching for properties or constructor dependencies.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function enableUseAnnotation($enable)
    {
        $this->canUseAnnotations = $enable;

        return $this;
    }

    /**
     * Checks if the use of annotations is enabled.
     *
     * @return bool
     */
    public function canUseAnnotations()
    {
        return $this->canUseAnnotations;
    }

    function __get($id)
    {
        return $this->get($id);
    }

    function __set($id, $entry)
    {
        $this->register($id, $entry);
    }

    /**
     * Counts and returns the number of registered
     * resolvers on this container
     *
     * return int
     */
    public function count()
    {
        return count($this->resolvers);
    }

    /**
     * Whether a $id exists.
     *
     * @param string $id
     *
     * @return bool
     */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    /**
     * Resolver $id to retrieve.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }

    /**
     * Resolver to set.
     *
     * @param string $id
     * @param mixed  $entry
     */
    public function offsetSet($id, $entry)
    {
        $this->register($id, $entry);
    }

    /**
     * Resolver to unset.
     *
     * @param string $id
     */
    public function offsetUnset($id)
    {
        $this->unregister($id);
    }
}
