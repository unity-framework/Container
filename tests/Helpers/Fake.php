<?php

namespace Helpers;

use ReflectionClass;

class Fake
{
    protected $instance;
    protected $reflectedClass;

    function __construct($instance)
    {
        $this->instance = $instance;
        $this->reflectedClass = new ReflectionClass($instance);
    }

    function __call($name, $arguments)
    {
        $method = $this->reflectedClass->getMethod($name);

        $method->setAccessible(true);
        return $method->invokeArgs($this->instance, $arguments);
    }
}