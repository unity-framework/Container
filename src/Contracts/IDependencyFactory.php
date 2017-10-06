<?php

namespace Unity\Component\Container\Contracts;

use Unity\Component\Container\Exceptions\NonInstantiableClassException;

/**
 * Interface IDependencyFactory.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IDependencyFactory
{
    /**
     * Sets the Container instance.
     *
     * @param IContainer $container
     */
    public function setContainer(IContainer $container);

    /**
     * Makes a `$class` instance.
     *
     * @param string $class        Class name.
     * @param array  $dependencies Constructor dependencies.
     *
     * @throws NonInstantiableClassException
     *
     * @return mixed|object
     */
    public function make($class, $dependencies = []);
}
