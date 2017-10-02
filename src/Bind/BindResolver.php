<?php

namespace Unity\Component\Container\Bind;

use Psr\Container\ContainerInterface;

/**
 * Class BindResolver.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolver
{
    protected $callback;
    protected $container;

    public function __construct(callable $callback, ContainerInterface $container)
    {
        $this->callback = $callback;
        $this->container = $container;
    }

    public function resolve()
    {
        return call_user_func($this->callback, $this->container);
    }
}
