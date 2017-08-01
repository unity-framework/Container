<?php

namespace Unity\Component\IoC;

use Unity\Component\IoC\Exceptions\ContainerException;

interface ResolverInterface
{
    /**
     * Resolve this resolver
     *
     * @return object|string|mixed
     */
    function resolve();

    /**
     * Gets the name
     * @return string
     */
    function getName();

    /**
     * Gets the entry
     * @return mixed
     */
    function getEntry();

    /**
     * Checks if this resolver has a
     * singleton instance
     * @return bool
     */
    function hasSingleton();

    /**
     * Gets the resolver instance
     *
     * Creates the instance if does'nt exists
     * and return it.
     *
     * If the entry isn't a existing class or a callback
     * it's returns the entry value
     *
     * @return object|string|mixed
     * @throws ContainerException
     */
    function getSingleton();

    /**
     * Set the singleton instance
     *
     * @param $instance
     * @return mixed
     */
    function setSingleton($instance);

    /**
     * Makes a new instance of the entry if
     * is a valid class or a Callable that
     * returns an instance, otherwise, returns
     * the entry
     *
     * @return callable|mixed|null|object|string
     * @throws ContainerException
     */
    function make();

    /**
     * Parameters to be given to the constructor
     * on build time
     *
     * @param array $params
     * @return $this
     */
    function with($params);

    /**
     * Parameters to bind with registered resolver on
     * the `ContainerInterface` instance
     *
     * @param array $to
     * @return $this
     */
    function bind($to);
}