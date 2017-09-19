<?php

namespace Unity\Component\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Dependency\DependencyResolverFactory;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;

/**
 * Class Container.
 *
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class Container implements IContainer
{
    protected $resolvers = [];
    protected $autowiring = true;

    /**
     * Resolves and returns the dependency registered on the first call.
     * Returns only the resolved dependency on subsequent calls.
     *
     * @param string $id Identifier of the dependency resolver.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function get($id)
    {
        if ($this->has($id)) {
            return $this->getDependencyResolver($id)->getSingleton();
        }

        throw new NotFoundException("No dependency resolver founded for \"{$id}\" on the container.");
    }

    /**
     * Checks if the container has a dependency resolver for the given $id.
     *
     * @param string $id Identifier for the resolver.
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
     * @param $id
     * @param null $params
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function make($id, $params = null)
    {
        if ($this->has($id)) {
            return $this->getDependencyResolver($id)->make($params);
        }

        throw new NotFoundException("No dependency resolver founded for \"{$id}\" on this container.");
    }

    /**
     * Register a dependency resolver.
     *
     * @param string $id
     * @param mixed  $entry The content that will be used to generate the dependency.
     *
     * @throws DuplicateIdException
     *
     * @return IDependencyResolver
     */
    public function register($id, $entry)
    {
        if ($this->has($id)) {
            throw new DuplicateIdException("The container already has a dependency resolver for \"{$id}\".");
        }
        return $this->setDependencyResolver($id, DependencyResolverFactory::make($id, $entry, $this));
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
            throw new NotFoundException("No dependency resolver founded for \"{$id}\" on this container.");
        }
        unset($this->resolvers[$id]);

        return $this;
    }

    /**
     * Replaces a registered resolver.
     *
     * This method does'nt replaces dependencies already resolved by this container.
     *
     * @param string $id
     * @param mixed  $entry The content that will be used to resolve the dependency.
     *
     * @return IDependencyResolver
     */
    public function replace($id, $entry)
    {
        return $this->setDependencyResolver($id, DependencyResolverFactory::make($id, $entry, $this));
    }

    /**
     * Gets the resolver.
     *
     * @param string $id Identifier of the resolver to get.
     *
     * @return mixed
     */
    public function getDependencyResolver($id)
    {
        return $this->resolvers[$id];
    }

    /**
     * Sets the resolver.
     *
     * @param string              $id       Identifier of the resolver to get.
     * @param IDependencyResolver $resolver
     *
     * @return IDependencyResolver
     */
    public function setDependencyResolver($id, IDependencyResolver $resolver)
    {
        return $this->resolvers[$id] = $resolver;
    }

    /**
     * Enable|Disable autowiring.
     *
     * @param bool $enable
     */
    public function enableAutowiring($enable)
    {
        $this->autowiring = $enable;
    }

    /**
     * Checks if autowiring is enabled.
     *
     * @return bool
     */
    public function canAutowiring()
    {
        return $this->autowiring;
    }
}
