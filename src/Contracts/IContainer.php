<?php

namespace Unity\Component\Container\Contracts;

use ArrayAccess;
use Countable;
use Psr\Container\ContainerInterface;

/**
 * Interface IContainer.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IContainer extends ContainerInterface, ArrayAccess, Countable
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
     * Replaces a registered resolver.
     *
     * This method does'nt replaces dependencies already resolved by the container.
     *
     * @param string $id
     * @param mixed  $entry
     *                      Content that will be used to resolve the dependency.
     *
     * @return DependencyResolver
     */
    public function replace($id, $entry);

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
     * @param string $class
     * @param mixed  $entry
     *
     * Binds a concrete class to an interface.
     *
     * Every time a class needs an argument of type `$class`,
     * the `$entry` will be resolved, and the value will be injected.
     *
     * Different of the register method, this will not throw an exception
     * if you register an bind with the same key twice, instead, it will
     * replace the old bind by this new one.
     *
     * @return Container
     */
    public function bind(string $class, $entry);

    /**
     * @param string $class
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function getBoundValue(string $class);

    /**
     * @param string $class
     *
     * @return bool
     */
    public function isBound(string $class);

    /**
     * Enable|Disable autowiring.
     *
     * Tells the container if it should auto wiring.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function enableAutowiring($enable);

    /**
     * Checks if injection is enabled.
     *
     * @return bool
     */
    public function canAutowiring();

    /**
     * Enable|Disable the use of annotations.
     *
     * Tells the container if it can inspect annotations
     * searching for properties or constructor dependencies.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function enableUseAnnotation($enable);

    /**
     * Checks if the use of annotations is enabled.
     *
     * @return bool
     */
    public function canUseAnnotations();
}
