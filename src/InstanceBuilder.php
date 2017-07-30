<?php

namespace Unity\Component\IoC;

class InstanceBuilder
{
    /**
     * @var bool $hasDependency
     */
    protected $hasDependency;

    /**
     * @var bool $autowiring
     */
    protected static $autowiring = true;

    /**
     * @var InstanceBuilder The current and top most
     * instance, used as Singleton access
     */
    protected static $instance;

    private function __clone(){}
    private function __construct(){}

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
     * Sets if InstanceBuilder can search for
     * dependencies in the entry and try resolve them
     *
     * @param bool $enabled
     */
    static function autowiring($enabled = true)
    {
        static::$autowiring = $enabled;
    }

    /**
     * Start the building of the instantiating
     *
     * @param $class
     * @return object
     */
    function resolve($class)
    {
        $refClass = $this->getReflectionClass($class);

        if($this->canAutowiring() && $this->hasDependencies())
            return $refClass->newInstanceArgs($this->getDependencies($refClass));
        else
            return $refClass->newInstanceWithoutConstructor();
    }

    /**
     * Checks if the autowiring is enabled
     *
     * @return bool
     */
    function canAutowiring()
    {
        return static::$autowiring;
    }

    /**
     * Returns an array with all necessary
     * dependencies to build the instantiating
     * class
     *
     * @param \ReflectionClass $refClass
     * @return array
     */
    function getDependencies(\ReflectionClass $refClass)
    {
        return array_map(function (\ReflectionType $type){

            if(class_exists($type)) {

                if(!$this->hasDependency)
                        $this->hasDependency = true;

                return (new InstanceBuilder)->resolve($type);
            }

            return null;
        }, $this->getParametersType($refClass));
    }

    /**
     * @return bool Return true if was founded at least one
     * required dependency for the instantiating class
     */
    function hasDependencies()
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
    function getParametersType(\ReflectionClass $refClass)
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
    function hasParameters(\ReflectionClass $refClass)
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
    function getReflectionClass($class = null)
    {
        return new \ReflectionClass($class);
    }
}