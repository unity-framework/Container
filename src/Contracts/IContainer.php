<?php

namespace Unity\Component\Container\Contracts;

use ArrayAccess;
use Countable;
use Psr\Container\ContainerInterface;
use Unity\Component\Container\Contracts\Dependency\IDependencyResolver;

/**
 * Interface IContainer.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IContainer extends ContainerInterface, ArrayAccess, Countable
{
    /**
     * Sets a dependency resolver.
     *
     * @param string $id
     * @param mixed  $entry Content that will be used to generate the dependency.
     *
     * @return IDependencyResolver
     */
    public function set($id, $entry);

    /**
     * Unset a resolver.
     *
     * @param string $id
     *
     * @return IContainer
     */
    public function unset($id);

    /**
     * Replaces a registered resolver.
     *
     * This method does'nt replaces dependencies already resolved by the container.
     *
     * @param string $id
     * @param mixed  $entry
     *                      Content that will be used to resolve the dependency.
     *
     * @return IDependencyResolver
     */
    public function replace($id, $entry);

    /**
     * Resolves and returns the dependency on the first call.
     * Returns the resolved dependency on subsequent calls.
     *
     * @param string $id Dependency resolver identifier.
     *
     * @return mixed
     */
    public function get($id);

    /**
     * Checks if the container has a dependency resolver for the given $id.
     *
     * @param string $id Dependency resolver identifier.
     *
     * @return bool
     */
    public function has($id);

    /**
     * Resolves and returns the registered dependency on every call.
     *
     * @param string $id     Dependency resolver identifier.
     * @param array  $params
     *
     * @return mixed
     */
    public function make($id, $params = null);

    /**
     * Sets a collection of service providers.
     *
     * @param array $serviceProviders An array containing `IServiceProvider`s.
     */
    public function setServiceProviders(array $serviceProviders);

    /**
     * Sets an `IServiceProviders`.
     *
     * @param IServiceProvider $serviceProvider A service provider.
     */
    public function setServiceProvider(IServiceProvider $serviceProvider);
}
