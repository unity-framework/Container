<?php

namespace Unity\Component\Container\Contracts;

/**
 * Interface IServiceProvider.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IServiceProvider
{
    /**
     * Register services.
     *
     * @param IContainer $container
     */
    public function register(IContainer $container);
}
