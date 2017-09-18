<?php

namespace Unity\Component\Container\Contracts;

use Psr\Container\ContainerExceptionInterface;
use Unity\Component\Container\Exceptions\ContainerException;

/**
 * Interface IDependencyResolver
 *
 * @package Unity\Component\Container\Exceptions
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IDependencyResolver
{
    /**
     * Resolves resolver content.
     *
     * @return mixed
     */
    function resolve();

    /**
     * Gets the resolver id.
     *
     * @return string
     */
    function getId();

    /**
     * Gets resolver entry.
     *
     * @return mixed
     */
    function getEntry();

    /**
     * Checks if resolver has a singleton instance.
     *
     * @return bool
     */
    function hasSingleton();

    /**
     * Resolves and returns content on the first call,
     * and returns only the resolved content on subsequent calls.
     *
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    function getSingleton();

    /**
     * Sets the singleton content.
     *
     * @param $content
     *
     * @return mixed
     */
    function setSingleton($content);

    /**
     * Resolves and returns a new dependency on every call.
     *
     * @param null $params
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    function make($params = null);

    /**
     * Parameters to be given to the constructor on build time.
     *
     * @param array $params
     *
     * @return $this
     */
    function give(array $params);

    /**
     * Gets the given parameters
     *
     * @return array
     */
    function getParams();

    /**
     * Binds others dependencies on container to this dependency.
     *
     * @param array $to
     *
     * @return $this
     */
    function bind(array $to);

    /**
     * Gets the given binds
     *
     * @return array
     */
    function getBinds();
}
