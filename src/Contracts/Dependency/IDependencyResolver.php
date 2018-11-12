<?php

namespace Unity\Component\Container\Contracts\Dependency;

use Unity\Component\Container\Contracts\IResolver;

/**
 * Interface IDependencyResolver.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IDependencyResolver extends IResolver
{
    /**
     * Resolves and returns a new dependency on every call.
     *
     * @param array $arguments Parameters that will be used
     *                         to construct the dependency.
     *
     *                         If a parameter already has a value given using the `give()`
     *                         method, that parameters will be override by the parameter
     *                         on the $parameters
     *
     * @return mixed
     */
    public function make($arguments = null);

    /**
     * Prevents the entry from being resolved
     * causing its immediately return.
     *
     * @param bool $enabled
     *
     * @return IDependencyResolver
     */
    public function protect($enabled = true);

    /**
     * Constructor arguments.
     *
     * @param array $arguments
     *
     * @return IDependencyResolver
     */
    public function give(array $arguments);

    /**
     * Gets the given arguments.
     *
     * @return array
     */
    public function getArguments();

    /**
     * @param string $class
     * @param mixed  $callback
     *
     * Binds a callback return value to a class.
     *
     * Every time a class needs an argument of type `$class`,
     * the `$callback` will be invoked, and the return value will be injected.
     *
     * Different of the register method, this will not throw an exception
     * if you register a bind with the same key twice, instead, it will
     * replace the old bind with this new one.
     *
     * @return static
     */
    public function bind($class, $callback);
}
