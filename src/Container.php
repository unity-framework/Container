<?php

namespace Unity\Component\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Bind\BindResolverFactory;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Dependency\DependencyResolverFactory;
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
    protected $binds      = [];
    protected $resolvers  = [];
    protected $autoInject = true;

    /**
     * Register a dependency resolver.
     *
     * @param string $id
     * @param mixed  $entry Content that will be used to generate the dependency.
     *
     * @throws DuplicateIdException
     *
     * @return DependencyResolver
     */
    public function register($id, $entry)
    {
        if ($this->has($id)) {
            throw new DuplicateIdException("The container already has a dependency resolver for \"{$id}\".");
        }

        return $this->resolvers[$id] = DependencyResolverFactory::make($id, $entry, $this);
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
            $this->throwNotFoundException($id);
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
     * @param mixed  $entry Content that will be used to resolve the dependency.
     *
     * @return DependencyResolver
     */
    public function replace($id, $entry)
    {
        return $this->resolvers[$id] = DependencyResolverFactory::make($id, $entry, $this);
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
            return $this->resolvers[$id]->getSingleton();
        }

        $this->throwNotFoundException($id);
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

        $this->throwNotFoundException($id);
    }

    /**
     * @param string $interface
     * @param mixed $entry
     *
     * Binds a concrete class to an interface.
     *
     * Every time
     *
     * @return Container
     */
    public function bind(string $interface, $entry)
    {
        $this->binds[$interface] = BindResolverFactory::make($interface, $entry, $this);

        return $this;
    }

    /**
     * @param string $interface
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function getBind(string $interface)
    {
        if ($this->hasBind($interface)) {
            return $this->binds[$interface]->resolve();
        }

        throw new NotFoundException("No resolver was bounded to interface \"{$interface}\" on the container.");
    }

    /**
     * @param $interface
     *
     * @return bool
     */
    public function hasBind(string $interface)
    {
        return isset($this->binds[$interface]);
    }

    /**
     * Enable|Disable auto inject.
     *
     * Tells the container if it should try auto inject
     * classes constructor dependencies.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function enableAutoInject($enable)
    {
        $this->autoInject = $enable;

        return $this;
    }

    /**
     * Checks if auto inject is enabled.
     *
     * @return bool
     */
    public function canAutoInject()
    {
        return $this->autoInject;
    }

    /**
     * @param $id
     *
     * @throws NotFoundException
     */
    protected function throwNotFoundException($id)
    {
        throw new NotFoundException("No dependency resolver was founded for \"{$id}\" on the container.");
    }
}
