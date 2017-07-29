<?php

namespace Unity\Component\IoC;

use Unity\Helpers\Str;
use Unity\Component\IoC\Exceptions\NonInjectableClassException;

class InstanceBuilder
{
    /**
     * @var bool $hasDependency Tells if was found at least
     * one required dependency for the instantiating class
     */
    protected $hasDependency;

    /**
     * @var InstanceBuilder The current and top most
     * instance, used as Singleton access
     */
    protected static $instance;

    private function __construct($innerInstance = false)
    {
        $this->innerInstance = $innerInstance;
    }

    private function __clone(){}

    /**
     * Build a class, instantiate
     * and return it for the Container
     *
     * @param $class
     * @return object
     */
    static function build($class)
    {
        if(is_null(static::$instance))
            static::$instance = new static;

        return static::$instance->resolve($class);
    }

    /**
     * Start the building of the instantiating
     *
     * @param $class
     * @return object
     */
    protected function resolve($class)
    {
        $refClass = $this->getReflectionClass($class);

        if($this->hasDependencies())
            return $refClass->newInstanceArgs($this->getDependencies($refClass));
        else
            return $refClass->newInstanceWithoutConstructor();
    }
    /**
     * Returns an array with all necessary
     * dependencies to build the instantiating
     * class
     *
     * @param \ReflectionClass $refClass
     * @return array
     */
    protected function getDependencies(\ReflectionClass $refClass)
    {
        return array_map(function (\ReflectionType $type){

            if(class_exists($type)) {

                if(!$this->hasDependency)
                        $this->hasDependency = true;

                $ib = new InstanceBuilder(true);

                return $ib->resolve($type);
            }

            return null;
        }, $this->getParametersType($refClass));
    }

    /**
     * @return bool Return true if was founded at least one
     * required dependency for the instantiating class
     */
    protected function hasDependencies()
    {
        return $this->hasDependency;
    }

    /**
     * Returns an array with all respective
     * parameters types in the constructor
     * of the instantiating class
     *
     * @param \ReflectionClass $refClass
     * @return array
     */
    protected function getParametersType(\ReflectionClass $refClass)
    {
        $params = [];

        if($this->hasParameters($refClass)) {
            $parameters = $refClass
                ->getConstructor()
                ->getParameters();

            foreach ($parameters as $param)
                $params[] = $param->getType();
        }

        return $params;
    }

    /**
     * Check if the instantiating class has
     * parameters in the constructor
     *
     * @param \ReflectionClass $refClass
     * @return bool
     */
    protected function hasParameters(\ReflectionClass $refClass)
    {
        $constructor = $refClass->getConstructor();

        return $constructor && ($constructor->getNumberOfParameters() > 0);
    }

    /**
     * Returns a \ReflectionClass instance based
     * on the provided $class parameter,
     *
     * @param string|null $class
     * @return \ReflectionClass
     */
    protected function getReflectionClass($class = null)
    {
        return new \ReflectionClass($class);
    }
}