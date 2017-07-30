<?php

namespace Unity\Component\IoC;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Unity\Component\IoC\Exceptions\DuplicateResolverNameException;
use Unity\Component\IoC\Exceptions\NotFoundException;
use Unity\Helpers\Str;

class Container implements ContainerInterface
{
    /**
     * Registered resolvers collection
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * @var bool $autowiring Set if the Container
     * can or not inject dependencies on @Injectable classes
     */
    protected $autowiring;

    function __construct($autowiring = true)
    {
        InstanceBuilder::autowiring($autowiring);
    }

    /**
     * Gets the resolved entry.
     *
     * @param string $name Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface No resolver with name **$name** was found on the container.
     * @throws ContainerExceptionInterface Error while trying to build **$name** dependencies.
     *
     * @return mixed Entry.
     */
    function get($name)
    {
        if($this->has($name))
            return $this->get($name)->instance();

        throw new NotFoundException("No resolver with name \"${name}\" was found on the container.");
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
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
     * the entry is a callback or an existing class,
     * otherwise returns the entry if
     *
     * @param $name
     * @return mixed
     * @throws NotFoundException
     */
    function make($name)
    {
        if($this->has($name))
            return $this->get($name)->make();

        throw new NotFoundException("No resolver with name \"${name}\" was found on the container.");
    }

    /**
     * Register a resolver
     *
     * @param string $name
     * @param \Closure|string $entry Identifier of the entry to register.
     * @throws DuplicateResolverNameException
     */
    function register($name, $entry)
    {
        if($this->has($name))
            throw new DuplicateResolverNameException("There's already a resolver with name \"${name}\" on the container");

        $this->resolvers[$name] = new Resolver($name, $entry, $this->autowiring);
    }

    /**
     * Defines if autowiring is enabled or not
     *
     * @param bool $enabled
     */
    function autowiring($enabled)
    {
        InstanceBuilder::autowiring($enabled);
    }
}