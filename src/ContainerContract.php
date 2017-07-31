<?php

namespace Unity\Component\IoC;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Unity\Component\IoC\Exceptions\DuplicateResolverNameException;
use Unity\Component\IoC\Exceptions\NotFoundException;

interface ContainerContract extends ContainerInterface
{
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
    function get($name);

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
    function has($name);

    /**
     * Makes a new instance of the resolved entry if
     * the entry is a callback or an existing class,
     * otherwise returns the entry if
     *
     * @param $name
     * @return mixed
     * @throws NotFoundException
     */
    function make($name);

    /**
     * Replace an existing resolver
     *
     * @param string $name
     * @param \Closure|string $entry Identifier of the entry to register.
     */
    function replace($name, $entry);

    /**
     * Register a resolver
     *
     * @param string $name
     * @param \Closure|string $entry Identifier of the entry to register.
     * @throws DuplicateResolverNameException
     */
    function register($name, $entry);

    /**
     * Unregister a resolver
     *
     * @param string $name
     * @throws NotFoundException
     */
    function unregister($name);
}