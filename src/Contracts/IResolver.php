<?php

namespace Unity\Component\Container\Contracts;

/**
 * Interface IResolver
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IResolver
{
    /**
     * @param string     $id
     * @param mixed      $entry
     * @param IContainer $container
     */
    function __construct(string $id, $entry, IContainer $container);

    /**
     * Resolves the entry.
     *
     * @return mixed
     */
    function resolve();
}