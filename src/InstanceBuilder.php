<?php

namespace Unity\Component\IoC;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class InstanceBuilder
{
    /** @var bool $autowiring */
    protected $autowiring = true;

    /** @var array $params */
    protected $params = [];

    /** @var array $binds */
    protected $binds = [];

    /** @var  ContainerContract $container */
    protected $container;

    /**
     * Checks if a parameter exists
     *
     * @param $paramName
     * @return bool
     */
    function hasParam($paramName)
    {
        return isset($this->params[$paramName]);
    }

    /**
     * Gets a parameter
     *
     * @param $paramName
     * @return mixed
     */
    function getParam($paramName)
    {
        return $this->params[$paramName];
    }

    /**
     * Gets all parameters
     *
     * @return array
     */
    function getParams()
    {
        return $this->params;
    }

    /**
     * Sets parameters
     *
     * @param $params
     */
    function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Checks if has a bind
     *
     * @param $bindName
     * @return bool
     */
    function hasBind($bindName)
    {
        return isset($this->binds[$bindName]) && $this->container->has($bindName);
    }

    /**
     * Get a bind
     *
     * @param $bindName
     * @return mixed
     */
    function getBind($bindName)
    {
        $registerName = $this->binds[$bindName];

        return $this->container->get($registerName);
    }

    /**
     * Gets all binds
     *
     * @return array
     */
    function getBinds()
    {
        return $this->binds;
    }

    /**
     * Sets binds
     *
     * @param $binds
     */
    function setBinds($binds)
    {
        $this->binds = $binds;
    }

    /**
     * Set the container
     *
     * Used to get the requested binds singleton
     *
     * @param ContainerContract $container
     */
    function setContainer(ContainerContract $container)
    {
        $this->container = $container;
    }

    /**
     * Gets the container
     *
     * @return ContainerContract
     */
    function getContainer()
    {
        return $this->container;
    }

    /**
     * Build a class, instantiate
     * and return it for the Container
     *
     * @param $classname
     * @return object
     */
    function build($classname)
    {
        /** Get a ReflectionInstance of the given class name */
        $reflectedClass = $this->reflectClass($classname);

        /**
         * If autowiring is enabled, we check if $reflectedClass hasParametersOnConstructor()
         * If it's true, then createInstanceWithParametersOnConstructor()
         */
        if($this->canAutowiring() && $this->hasParametersOnConstructor($reflectedClass))
            return $this->createInstanceWithParameters($reflectedClass);

        /**
        * If we're here, that means the $reflectedClass
        * has'nt a constructor, so...
        */
        return $this->createInstance($reflectedClass);
    }

    /**
     * Sets if InstanceBuilder can search for
     * dependencies in the entry and try resolve them
     *
     * @param bool $enabled
     */
    function enableAutowiring($enabled)
    {
        $this->autowiring = $enabled;
    }

    /**
     * Checks if the autowiring is enabled
     *
     * @return bool
     */
    function canAutowiring()
    {
        return $this->autowiring;
    }

    /**
     * Gets a ReflectionClass instance
     *
     * @param $classname
     * @return ReflectionClass
     */
    function reflectClass($classname)
    {
        return new ReflectionClass($classname);
    }

    /**
     * Gets the constructor
     *
     * @param ReflectionClass $rc
     * @return ReflectionMethod
     */
    function getConstructor(ReflectionClass $rc)
    {
        return $rc->getConstructor();
    }

    /**
     * Checks if there's a constructor
     *
     * @param ReflectionClass $rc
     * @return bool
     */
    function hasConstructor(ReflectionClass $rc)
    {
        return !is_null($rc->getConstructor());
    }

    /**
     * Check if the instantiating class has
     * parameters in the constructor
     *
     * @param ReflectionClass $rc
     * @return bool
     */
    function hasParametersOnConstructor(ReflectionClass $rc)
    {
        if($this->hasConstructor($rc)) {
            $constructor = $this->getConstructor($rc);

            return $constructor->getNumberOfParameters() > 0;
        }
    }

    /**
     * Returns an array with all respective
     * parameters types in the constructor
     * of the instantiating class
     *
     * @param ReflectionClass $rc
     * @return array
     */
    function getParametersNeededByTheConstructor(ReflectionClass $rc)
    {
        return $this->getConstructor($rc)
                    ->getParameters();
    }

    /**
     * Returns an array with all necessary
     * dependencies to build the instantiating
     * class
     *
     * @param ReflectionClass $rc
     * @return array
     */
    function getConstructorParametersValues(ReflectionClass $rc)
    {
        return array_map(
            function (ReflectionParameter $parameter){

                $paramName = $parameter->getName();

                if($this->hasBind($paramName))
                    return $this->getBind($paramName);

                $type = (string)$parameter->getType();

                if(class_exists($type)) {
                    return (new InstanceBuilder)->build($type);
                }

                if($this->hasParam($paramName))
                    return $this->getParam($paramName);
            },
            $this->getParametersNeededByTheConstructor($rc)
        );
    }

    /**
     * Creates a new instance of the reflected class
     * and put all needed arguments on the constructor
     * if needed
     *
     * @param ReflectionClass $rc
     * @return object
     */
    function createInstanceWithParameters(ReflectionClass $rc)
    {
        $constructorParams = $this->getConstructorParametersValues($rc);

        return $rc->newInstanceArgs($constructorParams);
    }

    /**
     * Creates a new instance of the reflected class
     * without a constructor
     *
     * @param ReflectionClass $rc
     * @return object
     */
    function createInstance(ReflectionClass $rc)
    {
        return $rc->newInstanceWithoutConstructor();
    }
}
