<?php

namespace Helpers;

class WithConstructorDependencies
{
    public function __construct(WithConstructor $withConstructor, WithoutConstructor $withoutConstructor)
    {
    }
}
