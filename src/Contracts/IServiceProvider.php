<?php

namespace Unity\Component\Container\Contracts;

/**
 * Interface IServiceProvider.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IServiceProvider
{
    public function register() : array;
}
