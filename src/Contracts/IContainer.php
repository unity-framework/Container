<?php

namespace Unity\Component\Container\Contracts;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Unity\Component\Container\Container;
use Unity\Component\Container\Dependency\DependencyResolver;
use Unity\Component\Container\Exceptions\DuplicateIdException;
use Unity\Component\Container\Exceptions\NotFoundException;

/**
 * Interface IContainer.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IContainer extends ContainerInterface
{
    /**
     * Register a dependency resolver.
     *
     * @param string $id
     * @param mixed  $entry Content that will be used to generate the dependency.
     *
     * @throws DuplicateIdException
     *
     * @return DependencyResolver
     */
    public function register($id, $entry);

    /**
     * Unregister a resolver.
     *
     * @param string $id
     *
     * @throws NotFoundException
     *
     * @return Container
     */
    public function unregister($id);

    /**
     * @param string $interface
     * @param mixed $entry
     *
     * @return Container
     */
    public function bind(string $interface, $entry);

    /**
     * @param $interface
     *
     * @return mixed
     */
    public function getBind(string $interface);

    /**
     * @param $type
     *
     * @return bool
     */
    public function hasBind(string $type);

    /**
     * Resolves and returns the dependency on the first call.
     * Returns the resolved dependency on subsequent calls.
     *
     * @param string $id Dependency resolver identifier.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
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
     * @param string     $id     Dependency resolver identifier.
     * @param array|null $params
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function make($id, $params = null);

    /**
     * Replaces a registered resolver.
     *
     * This method does'nt replaces dependencies already resolved by the container.
     *
     * @param string $id
     * @param mixed  $entry Content that will be used to resolve the dependency.
     *
     * @return DependencyResolver
     */
    public function replace($id, $entry);

    /**
     * Enable|Disable auto inject.
     *
     * Tells the container if it should try auto inject
     * classes constructor dependencies.
     *
     * @param bool $enable
     */
    public function enableAutoInject($enable);

    /**
     * Checks if auto inject is enabled.
     *
     * @return bool
     */
    public function canAutoInject();
}
