<?php

namespace Unity\Component\Container\Contracts;

/**
 * Interface IResolver.
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
     * causing its imediactly return.
     *
     * @param bool $enabled
     *
     * @return DependencyResolver
     */
    public function protect($enabled = true);

    /**
     * Constructor arguments.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function give(array $arguments);

    /**
     * Gets the given arguments.
     *
     * @return array
     */
    public function getArguments();
}
