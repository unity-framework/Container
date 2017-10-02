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
}
