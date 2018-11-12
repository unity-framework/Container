<?php

namespace Unity\Component\Container\Contracts;

/**
 * Interface IContainer.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
interface IContainerManager
{
    /**
     * Enable|Disable auto depependencies resolution.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function autoResolve($enable);

    /**
     * Enable|Disable dependencies resolution using annotations.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function canUseAnnotations($enable);

    /**
     * Builds an `IContainer` instance.
     *
     * @return IContainer
     */
    public function build();
}
