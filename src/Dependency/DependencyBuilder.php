<?php

namespace Unity\Component\Container\Dependency;

use ReflectionClass;
use ReflectionMethod;
use Unity\Component\Container\Contracts\IUnityContainer;
use Unity\Component\Container\Exceptions\MissingConstructorArgumentException;

/**
 * Class DependencyBuilder.
 *
 * Builds dependencies using Reflection.
 *
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyBuilder
{
    protected $autowiring = true;
    protected $params = [];
    protected $binds = [];
    protected $container;

    /**
     * DependencyBuilder constructor.
     *
     * @param IUnityContainer $container
     * @param array           $params
     * @param array           $binds
     */
    public function __construct(IUnityContainer $container, $params = null, $binds = null)
    {
        $this->container = $container;
        $this->params = $params;
        $this->binds = $binds;
    }

    /**
     * Checks if a parameter needed to construct a dependency was given.
     *
     * @param $parameterName
     *
     * @return bool
     */
    public function hasParam($parameterName)
    {
        return isset($this->params[$parameterName]);
    }

    /**
     * Gets a parameter needed to construct a dependency.
     *
     * @param $parameterName
     *
     * @return mixed
     */
    public function getParam($parameterName)
    {
        return $this->params[$parameterName];
    }

    /**
     * Gets all given parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Checks if a bind exists.
     *
     * @param $bindName
     *
     * @return bool
     */
    public function hasBind($bindName)
    {
        return isset($this->binds[$bindName]) && $this->container->has($bindName);
    }

    /**
     * Gets a bind.
     *
     * @param $bindName
     *
     * @return mixed
     */
    public function getBind($bindName)
    {
        $registerName = $this->binds[$bindName];

        return $this->container->get($registerName);
    }

    /**
     * Gets all binds.
     *
     * @return array
     */
    public function getBinds()
    {
        return $this->binds;
    }

    /**
     * Gets the container instance.
     *
     * @return IUnityContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Constructs and returns a class instance.
     *
     * @param $className
     *
     * @return object
     */
    public function build($className)
    {
        /* Get a ReflectionInstance of the given class name */
        $rc = $this->reflectClass($className);

        /*
         * We need to check if $rc `hasParametersOnConstructor()`,
         * if it's true, then `createInstanceWithParametersOnConstructor()`
         */
        if ($this->hasParametersOnConstructor($rc)) {
            return $this->createInstanceWithParameters($rc);
        }

        /*
        * If we're here, that means $rc has'nt a constructor, so we just...
        */
        return $this->createInstance($rc);
    }

    /**
     * Gets a ReflectionClass instance for $class.
     *
     * @param string $class Class to be reflected.
     *
     * @return ReflectionClass
     */
    public function reflectClass($class)
    {
        return new ReflectionClass($class);
    }

    /**
     * Gets the constructor of the reflected class.
     *
     * @param ReflectionClass $rc
     *
     * @return ReflectionMethod
     */
    public function getConstructor(ReflectionClass $rc)
    {
        return $rc->getConstructor();
    }

    /**
     * Checks if the reflected class has constructor.
     *
     * @param ReflectionClass $rc
     *
     * @return bool
     */
    public function hasConstructor(ReflectionClass $rc)
    {
        return !is_null($rc->getConstructor());
    }

    /**
     * Checks if the reflected class has parameters in the constructor.
     *
     * @param ReflectionClass $rc
     *
     * @return bool
     */
    public function hasParametersOnConstructor(ReflectionClass $rc)
    {
        if ($this->hasConstructor($rc)) {
            $constructor = $this->getConstructor($rc);

            return $constructor->getNumberOfParameters() > 0;
        }

        return false;
    }

    /**
     * Returns an array with all parameters required to construct
     * the reflected class.
     *
     * @param ReflectionClass $rc
     *
     * @return array
     */
    public function getParametersRequiredToConstructReflectedClass(ReflectionClass $rc)
    {
        return $this->getConstructor($rc)->getParameters();
    }

    /**
     * Returns an array with all given parameters to construct
     * the reflected class.
     *
     * @param $requiredParameters
     *
     * @return array
     */
    public function getGivenConstructorParametersData($requiredParameters)
    {
        $givenParametersData = [];

        /*
         * For each required parameter, get the given para value
         */
        foreach ($requiredParameters as $param) {
            $paramName = $param->getName();

            /*
             * If there's a bind for this param, get,
             * store it and jump to the next parameter
             */
            if ($this->hasBind($paramName)) {
                $givenParametersData[$paramName] = $this->getBind($paramName);

                continue;
            }

            /*
             * If there's an explicit param value
             * for this param, get, store it and jump to the next parameter
             */
            if ($this->hasParam($paramName)) {
                $givenParametersData[$paramName] = $this->getParam($paramName);

                continue;
            }

            $type = (string) $param->getType();

            /*
             * If canAutowiring is enabled and the
             * required param is a class, get and store it
             * in the required parameters
             */
            if ($this->container->canAutowiring() && class_exists($type)) {
                $givenParametersData[$paramName] = (new self($this->getContainer()))->build($type);
            }
        }

        return $givenParametersData;
    }

    /**
     * Ensure that no needed parameter is missing.
     *
     * @param $params
     * @param $givenParametersValues
     * @param $rc
     *
     * @throws MissingConstructorArgumentException
     */
    public function ensureNoMissingConstructorParameter($params, $givenParametersValues, $rc)
    {
        $rcName = $rc->getName();

        /*
         * We need to ensure that all required parameters to
         * construct the reflected class were given, for it,
         * we must check if the $givenParametersValues contains
         * all parameters names in your keys, except the optional parameters
         */
        foreach ($params as $param) {
            $paramName = $param->getName();

            /* Optional parameters should be ignored */
            if (!$param->isOptional() && !array_key_exists($paramName, $givenParametersValues)) {
                throw new MissingConstructorArgumentException("Missing argument \${$paramName} for {$rcName}::__construct()");
            }
        }
    }

    /**
     * Creates a new instance of the reflected class
     * and put all required arguments on the constructor
     * if needed.
     *
     * @param ReflectionClass $rc
     *
     * @return object
     */
    public function createInstanceWithParameters(ReflectionClass $rc)
    {
        $params = $this->getParametersRequiredToConstructReflectedClass($rc);
        $givenConstructorParametersData = $this->getGivenConstructorParametersData($params);

        $this->ensureNoMissingConstructorParameter($params, $givenConstructorParametersData, $rc);

        return $rc->newInstanceArgs($givenConstructorParametersData);
    }

    /**
     * Creates a new instance of the reflected class
     * without a constructor.
     *
     * @param ReflectionClass $rc
     *
     * @return object
     */
    public function createInstance(ReflectionClass $rc)
    {
        return $rc->newInstanceWithoutConstructor();
    }
}
