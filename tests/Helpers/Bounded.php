<?php

namespace Helpers;

class Bounded
{
    protected $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
}
