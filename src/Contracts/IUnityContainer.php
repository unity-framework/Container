<?php

namespace Unity\Component\Container\Contracts;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;

/**
 * Interface IUnityContainer
 *
 * @package Unity\Component\Container\Contracts
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IUnityContainer extends ContainerInterface
{
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
    function get($id);

    /**
     * Checks if the container has a dependency resolver for the given $id.
     *
     * @param string $id Identifier for the resolver.
     *
     * @return bool
     */
    function has($id);

    /**
     * Resolves and returns the registered dependency on every call.
     *
     * @param $id
     *
     * @param null $params
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    function make($id, $params = null);

    /**
     * Register a dependency resolver.
     *
     * @param string $id
     * @param mixed $entry The content that will be used to generate the dependency.
     *
     * @throws DuplicateIdException
     *
     * @return IDependencyResolver
     */
    function register($id, $entry);

    /**
     * Unregister a resolver.
     *
     * @param string $id
     *
     * @throws NotFoundException
     *
     * @return IUnityContainer
     */
    function unregister($id);

    /**
     * Replaces a registered resolver.
     *
     * This method does'nt replaces dependencies already resolved by this container.
     *
     * @param string $id
     * @param mixed $entry The content that will be used to resolve the dependency.
     *
     * @return IDependencyResolver
     */
    function replace($id, $entry);

    /**
     * Gets the resolver.
     *
     * @param string $id Identifier of the resolver to get.
     *
     * @return mixed
     */
    function getDependencyResolver($id);

    /**
     * Sets the resolver.
     *
     * @param string $id Identifier of the resolver to get.
     * @param IDependencyResolver $resolver
     *
     * @return IDependencyResolver
     */
    function setDependencyResolver($id, IDependencyResolver $resolver);

    /**
     * Enable|Disable autowiring
     *
     * @param bool $enable
     */
    function enableAutowiring($enable);

    /**
     * Checks if autowiring is enabled
     *
     * @return bool
     */
    function canAutowiring();
}