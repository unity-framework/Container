<?php

namespace Unity\Tests\Container\Helpers;

class Foo implements IFoo
{
    public function __construct(Bar $bar)
    {
    }
}
