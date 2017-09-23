<?php

namespace Unity\Component\Container\Bind;

use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Contracts\IResolver;

/**
 * Class BindResolver.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolver implements IResolver
{
    protected $id;
    protected $entry;
    protected $container;

    function __construct(string $id, $entry, IContainer $container)
    {
        $this->id        = $id;
        $this->entry     = $entry;
        $this->container = $container;
    }

    function resolve()
    {
        return call_user_func($this->entry, $this->container);
    }
}