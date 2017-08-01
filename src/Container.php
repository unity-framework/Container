<?php

namespace Unity\Component\IoC;

use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Unity\Component\IoC\Exceptions\DuplicateResolverNameException;
use Unity\Component\IoC\Exceptions\NotFoundException;

class Container implements ContainerContract
{
    /**
     * Registered resolvers collection.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * Gets the resolved entry.
     *
     * @param string $name Identifier of the resolver to look for.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return mixed Entry.
     */
    function get($name)
    {
        if($this->has($name))
            return $this->getResolver($name)->getSingleton();

        throw new NotFoundException("No resolver with name \"${name}\" was found on the container.");
    }

    /**
     * Returns `true` if the container can return an entry for the given identifier.
     *
     * `has($name)` returning true does not mean that `get($name)` will not throw an exception.
     * It does however mean that `get($name)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $name Identifier of the entry to look for.
     *
     * @return bool
     */
    function has($name)
    {
        return isset($this->resolvers[$name]);
    }

    /**
     * Makes a new instance of the resolved entry if
     * the entry is a `Callable` or an existing class,
     * otherwise returns the entry
     *
     * @param $name
     * @return mixed
     * @throws NotFoundException
     */
    function make($name)
    {
        if($this->has($name))
            return $this->getResolver($name)->make();

        throw new NotFoundException("No resolver with name \"${name}\" was found on the container.");
    }

    /**
     * Register a resolver
     *
     * @param string $name
     * @param \Closure|string $entry Identifier of the entry to register.
     * @throws DuplicateResolverNameException
     *
     * @return Resolver
     */
    function register($name, $entry)
    {
        if($this->has($name))
            throw new DuplicateResolverNameException("There's already a resolver with name \"${name}\" on the container");

        return $this->setResolver($name, ResolverFactory::Make($name, $entry, $this));
    }

    /**
     * Unregister a resolver
     *
     * @param string $name
     * @throws NotFoundException
     */
    function unregister($name)
    {
        if(!$this->has($name))
            throw new NotFoundException("No resolver with name \"${name}\" was found on the container.");

        unset($this->resolvers[$name]);
    }

    /**
     * Replace an existing resolver
     *
     * This method don't replace instances already injected
     *
     * @param string $name
     * @param \Closure|string $entry Identifier of the entry to register.
     *
     * @return Resolver
     */
    function replace($name, $entry)
    {
        return $this->setResolver($name, new Resolver(
            $name,
            $entry,
            $this
        ));
    }

    /**
     * Gets the resolver.
     *
     * @param string $name Identifier of the resolver to get.
     * @return mixed
     */
    function getResolver($name)
    {
        return $this->resolvers[$name];
    }

    /**
     * Sets the resolver.
     *
     * @param string $name Identifier of the resolver to get.
     * @param Resolver $resolver
     *
     * @return Resolver
     */
    function setResolver($name, ResolverInterface $resolver)
    {
        return $this->resolvers[$name] = $resolver;
    }

    function enableAutowiring()
    {

    }
}