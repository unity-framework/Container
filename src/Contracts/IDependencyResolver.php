<?php

namespace Unity\Component\Container\Contracts;

use Psr\Container\ContainerExceptionInterface;
use Unity\Component\Container\Exceptions\ContainerException;

/**
 * Interface IDependencyResolver.
 *
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
    public function resolve();

    /**
     * Gets the resolver id.
     *
     * @return string
     */
    public function getId();

    /**
     * Gets resolver entry.
     *
     * @return mixed
     */
    public function getEntry();

    /**
     * Checks if resolver has a singleton instance.
     *
     * @return bool
     */
    public function hasSingleton();

    /**
     * Resolves and returns content on the first call,
     * and returns only the resolved content on subsequent calls.
     *
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function getSingleton();

    /**
     * Sets the singleton content.
     *
     * @param $content
     *
     * @return mixed
     */
    public function setSingleton($content);

    /**
     * Resolves and returns a new dependency on every call.
     *
     * @param null $parameters
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    public function make($parameters = null);

    /**
     * Parameters to be given to the constructor on build time.
     *
     * @param array $params
     *
     * @return $this
     */
    public function give(array $params);

    /**
     * Gets the given parameters.
     *
     * @return array
     */
    public function getGivenParams();

    /**
     * Binds others dependencies on container to this dependency.
     *
     * @param array $to
     *
     * @return $this
     */
    public function bind(array $to);

    /**
     * Gets the given binds.
     *
     * @return array
     */
    public function getBinds();
}
