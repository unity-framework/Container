<?php

namespace Helpers;

class WithProperties
{
    /**
     * @inject
     *
     * @var \Helpers\Bar
     */
    protected $bar;

    /**
     * @inject
     *
     * @var \stdClass
     */
    protected $std;

    /*
     * @inject value.
     *
     * Used to test if DependencyFactory can inject
     * container entries.
     *
     * In this case, our entry is a boolean, but it can be
     * any kind of value.
     *
     * @var bool
     */
    //protected $boolean;
}
