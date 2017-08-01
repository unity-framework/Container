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
     * Checks if a bind exists
     *
     * @param $bindName
     * @return bool
     */
    function hasBind($bindName)
    {
        return isset($this->binds[$bindName]) && $this->container->has($bindName);
    }

    /**
     * Gets a bind
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
     * Sets the container
     *
     * Used to get the requested bind singleton
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
     * Builds a class, instantiate
     * and returns it
     *
     * @param $className
     * @return object
     */
    function build($className)
    {
        /** Get a ReflectionInstance of the given class name */
        $rc = $this->reflectClass($className);

        /**
         * We need to check if $rc `hasParametersOnConstructor()`,
         * if it's true, then `createInstanceWithParametersOnConstructor()`
         */
        if($this->hasParametersOnConstructor($rc))
            return $this->createInstanceWithParameters($rc);

        /**
        * If we're here, that means that $rc
        * has'nt a constructor, so, just...
        */
        return $this->createInstance($rc);
    }

    /**
     * Sets if InstanceBuilder can search for
     * dependencies in the entry and resolve them
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
     * Gets the constructor of the required class
     *
     * @param ReflectionClass $rc
     * @return ReflectionMethod
     */
    function getConstructor(ReflectionClass $rc)
    {
        return $rc->getConstructor();
    }

    /**
     * Checks if the required class has constructor
     *
     * @param ReflectionClass $rc
     * @return bool
     */
    function hasConstructor(ReflectionClass $rc)
    {
        return !is_null($rc->getConstructor());
    }

    /**
     * Checks if the building class has
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

        return false;
    }

    /**
     * Returns an array with all parameters
     * types of each parameter on the building
     * class constructor
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
     * parameters to build the building class
     *
     * @param $params
     * @return array
     * @internal param ReflectionClass $rc
     */
    function getConstructorParametersValues($params)
    {
        $paramsValues = [];

        /**
        * For each parameter, get the respective value
        */
        foreach ($params as $param) {
            $paramName = $param->getName();

            /**
            * If there's a bind for this param, get,
            * store it and jump to the next parameter
            */
            if($this->hasBind($paramName)) {
                $paramsValues[$paramName] = $this->getBind($paramName);

                continue;
            }

            /**
            * If there's an explicit param value
            * for this param, get, store it and jump to the next parameter
            */
            if($this->hasParam($paramName)) {
                $paramsValues[$paramName] = $this->getParam($paramName);

                continue;
            }

            $type = (string)$param->getType();

            /**
            * If canAutowiring is enabled and the
            * required param is a class, get and store it
            */
            if($this->canAutowiring() && class_exists($type))
                $paramsValues[$paramName] = (new InstanceBuilder)->build($type);
        }

        return $paramsValues;
    }

    /**
     * Ensure that no needed parameter is missing
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
         * parameters to construct the building
         * class were given, for it, we must check
         * if the $paramValues contains all parameters
         * names in your keys, except the optional parameters
         */
        foreach ($params as $param) {
            $paramName = $param->getName();

            /** Optional parameters should be ignored */
            if(!$param->isOptional() && !array_key_exists($paramName, $paramValues))
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
