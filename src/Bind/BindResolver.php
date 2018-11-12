<?php

namespace Unity\Component\Container\Bind;

use Unity\Component\Container\Contracts\Bind\IBindResolver;
use Unity\Component\Container\Contracts\IContainer;

/**
 * Class BindResolver.
 *
 * Binds values to a class.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolver implements IBindResolver
{
    protected $callback;
    protected $container;

    public function __construct(callable $callback, IContainer $container)
    {
        $this->callback = $callback;
        $this->container = $container;
    }

    public function resolve()
    {
        return call_user_func($this->callback, $this->container);
    }
}
