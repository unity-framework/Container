<?php

namespace Unity\Component\IoC;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Unity\Component\IoC\Exceptions\MissingConstructorArgumentException;

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
     * @param $className
     * @return object
     */
    function build($className)
    {
        /** Get a ReflectionInstance of the given class name */
        $reflectedClass = $this->reflectClass($className);

        /**
         * If autowiring is enabled, we check if $reflectedClass hasParametersOnConstructor()
         * If it's true, then createInstanceWithParametersOnConstructor()
         */
        if($this->hasParametersOnConstructor($reflectedClass))
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
     * @param $className
     * @return ReflectionClass
     */
    function reflectClass($className)
    {
        return new ReflectionClass($className);
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
     * @param $params
     * @return array
     * @internal param ReflectionClass $rc
     */
    function getConstructorParametersValues($params)
    {
        $paramsValues = [];

        foreach ($params as $param) {
            $paramName = $param->getName();

            if($this->hasBind($paramName)) {
                $paramsValues[$paramName] = $this->getBind($paramName);

                continue;
            }

            $type = (string)$param->getType();

            if($this->hasParam($paramName)) {
                $paramsValues[$paramName] = $this->getParam($paramName);

                continue;
            }

            /** Its resolves class dependencies recursively */
            if($this->canAutowiring() && class_exists($type))
                $paramsValues[$paramName] = (new InstanceBuilder)->build($type);
        }

        return $paramsValues;
    }

    /**
     * Make sure no needed parameters is missing
     *
     * @param $params
     * @param $paramValues
     * @param $rc
     * @throws MissingConstructorArgumentException
     */
    function ensureNoMissingConstructorParameter($params, $paramValues, $rc)
    {
        $rcName = $rc->getName();

        /**
         * We need to ensure that all required
         * parameters to construct the requested
         * class were given, for it, just check if
         * the $paramValues contains all parameters
         * names in your keys
         */
        foreach ($params as $param) {
            $paramName = $param->getName();

            if(!array_key_exists($paramName, $paramValues))
                throw new MissingConstructorArgumentException("Missing argument \${$paramName} for {$rcName}::__construct()");
        }
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
        $params = $this->getParametersNeededByTheConstructor($rc);
        $paramsValues = $this->getConstructorParametersValues($params);

        $this->ensureNoMissingConstructorParameter($params, $paramsValues, $rc);

        return $rc->newInstanceArgs($paramsValues);
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
