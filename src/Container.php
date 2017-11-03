<?php

namespace Unity\Component\Container;

use Psr\Container\NotFoundExceptionInterface;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;
use Unity\Contracts\Container\Dependency\IDependencyFactory;
use Unity\Contracts\Container\Dependency\IDependencyResolver;
use Unity\Contracts\Container\Factories\IBindResolverFactory;
use Unity\Contracts\Container\Factories\IDependencyResolverFactory;
use Unity\Contracts\Container\IContainer;
use Unity\Contracts\Container\IServiceProvider;

/**
 * Class Container.
 *
 * Dependencies manager.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class Container implements IContainer
{
    /** @var IDependencyResolver[] */
    protected $resolvers = [];

    /** @var IDependencyFactory */
    protected $dependencyFactory;

    /** @var IDependencyResolverFactory */
    protected $dependencyResolverFactory;
    /** @var IBindResolverFactory */
    protected $bindResolverFactory;

    /**
     * Container constructor.
     *
     * @param IDependencyFactory         $dependencyFactory
     * @param IDependencyResolverFactory $dependencyResolverFactory
     * @param IBindResolverFactory       $bindResolverFactory
     */
    public function __construct(
        IDependencyFactory $dependencyFactory,
        IDependencyResolverFactory $dependencyResolverFactory,
        IBindResolverFactory $bindResolverFactory
    ) {
        $this->dependencyFactory = $dependencyFactory;
        $this->dependencyResolverFactory = $dependencyResolverFactory;
        $this->bindResolverFactory = $bindResolverFactory;
    }

    /**
     * Sets a dependency resolver.
     *
     * @param string $id
     * @param mixed  $entry Content that will be used to generate the dependency.
     *
     * @throws DuplicateIdException
     *
     * @return IDependencyResolver
     */
    public function set($id, $entry)
    {
        if ($this->has($id)) {
            throw new DuplicateIdException("The container already has a dependency resolver for id \"{$id}\".");
        }

        return $this->resolvers[$id] = $this->dependencyResolverFactory->make(
                $entry,
                $this->dependencyFactory,
                $this->bindResolverFactory,
                $this
            );
    }

    /**
     * Unsets a resolver.
     *
     * @param string $id
     *
     * @throws NotFoundException
     *
     * @return Container
     */
    public function unset($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("No dependency resolver was founded for id \"{$id}\" on the container.");
        }

        unset($this->resolvers[$id]);

        return $this;
    }

    /**
     * Replaces a resolver.
     *
     * This method does'nt replaces dependencies already resolved by the container.
     *
     * @param string $id
     * @param mixed  $entry Content that will be used to resolve the dependency.
     *
     * @return IDependencyResolver
     */
    public function replace($id, $entry)
    {
        $resolver = $this->dependencyResolverFactory->make(
            $entry,
            $this->dependencyFactory,
            $this->bindResolverFactory,
            $this
        );

        return $this->resolvers[$id] = $resolver;
    }

    /**
     * Resolves and returns the dependency on the first call.
     * Returns the resolved dependency on subsequent calls.
     *
     * @param string $id Dependency resolver identifier.
     *
     * @throws NotFoundExceptionInterface
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
        return array_key_exists($id, $this->resolvers);
    }

    /**
     * Resolves and returns the registered dependency on every call.
     *
     * @param string $id     Dependency resolver identifier.
     * @param array  $params
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function make($id, $params = [])
    {
        if ($this->has($id)) {
            return $this->resolvers[$id]->make($params);
        }

        throw new NotFoundException("No dependency resolver was founded for id \"{$id}\" on the container.");
    }

    /**
     * Sets an `IServiceProviders`.
     *
     * @param IServiceProvider $serviceProvider A service provider.
     */
    public function setServiceProvider(IServiceProvider $serviceProvider)
    {
        $serviceProvider->register($this);
    }

    /**
     * Sets a collection of service providers.
     *
     * @param array $serviceProviders An array containing `IServiceProvider`s.
     */
    public function setServiceProviders(array $serviceProviders)
    {
        foreach ($serviceProviders as $serviceProvider) {
            $this->setServiceProvider(new $serviceProvider);
        }
    }

    public function __get($id)
    {
        return $this->get($id);
    }

    public function __set($id, $entry)
    {
        $this->set($id, $entry);
    }

    /**
     * Counts and returns the number of registered
     * resolvers on this container.
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
        $this->set($id, $entry);
    }

    /**
     * Resolver to unset.
     *
     * @param string $id
     */
    public function offsetUnset($id)
    {
        $this->unset($id);
    }
}
