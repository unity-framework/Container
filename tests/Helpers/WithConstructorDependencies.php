<?php

namespace Helpers;

class WithConstructorDependencies
{
    function __construct(WithConstructor $withConstructor, WithoutConstructor $withoutConstructor)
    {
    }
}