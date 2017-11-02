<?php

namespace Unity\Tests\Container\Helpers;

class Bounded
{
    protected $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
}
