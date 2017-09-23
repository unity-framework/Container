<?php

namespace Unity\Component\Container\Bind;

use Unity\Component\Container\Contracts\IContainer;

/**
 * Class BindResolverFactory.
 *
 * Makes BindResolver::class instances
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolverFactory
{
    /**
     * @param $id
     * @param $entry
     * @param IContainer $container
     *
     * @return BindResolver
     */
    public static function make($id, $entry, IContainer $container)
    {
        return new BindResolver($id, $entry, $container);
    }
}
