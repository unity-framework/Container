<?php

namespace Unity\Tests\Container\Helpers;

class WithConstructorParameterBind
{
    public function __construct(WithConstructor $withConstructor, WithoutConstructor $withoutConstructor)
    {
    }
}
