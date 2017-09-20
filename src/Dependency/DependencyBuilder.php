<?php

namespace Unity\Component\Container\Dependency;

use ReflectionClass;
use ReflectionMethod;
use Unity\Component\Container\Contracts\IContainer;
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
    protected $container;
    protected $givenParameters     = [];
    protected $containerBinds      = [];

    /**
     * DependencyBuilder constructor.
     *
     * @param IContainer      $container
     * @param array           $params
     * @param array           $binds
     */
    public function __construct(IContainer $container, $params = null, $binds = null)
    {
        $this->container = $container;
        $this->givenParameters = $params;
        $this->containerBinds = $binds;
    }

    /**
     * Checks if a parameter needed to construct a dependency was given.
     *
     * @param $parameterName
     *
     * @return bool
     */
    public function hasGivenParameter($parameterName)
    {
        return isset($this->givenParameters[$parameterName]);
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
        return $this->givenParameters[$parameterName];
    }

    /**
     * Gets all given parameters.
     *
     * @return array
     */
    public function getGivenParameters()
    {
        return $this->givenParameters;
    }

    /**
     * Checks if a bind exists.
     *
     * @param $containerBindName
     *
     * @return bool
     */
    public function hasContainerBind($containerBindName)
    {
        return isset($this->containerBinds[$containerBindName]) && $this->container->has($containerBindName);
    }

    /**
     * Gets a bind.
     *
     * @param $containerBindName
     *
     * @return mixed
     */
    public function getContainerBind($containerBindName)
    {
        $id = $this->containerBinds[$containerBindName];

        return $this->container->get($id);
    }

    /**
     * Gets all binds.
     *
     * @return array
     */
    public function getContainerBinds()
    {
        return $this->containerBinds;
    }

    /**
     * Gets the container instance.
     *
     * @return IContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Constructs and returns a class instance.
     *
     * @param $class
     *
     * @return object
     */
    public function build($class)
    {
        /* Get a ReflectionClass instance of the given class */
        $rc = $this->reflectClass($class);

        /*
         * We need to check if $rc `hasParametersOnConstructor()`,
         * if it's true, then `createInstanceWithParametersOnConstructor()`
         */
        if ($this->hasParametersOnConstructor($rc)) {
            return $this->createInstanceWithParameters($rc);
        }

        /*
        * If we're here, that means $rc has'nt a constructor,
        * so we just instantiate it...
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
        $givenData = [];

        /*
         * For each required parameter, get the correspondent given data.
         */
        foreach ($requiredParameters as $param) {
            $paramName = $param->getName();

            /*
             * If there's a bind for this param, get and
             * store it, then jump to the next parameter.
             */
            if ($this->hasContainerBind($paramName)) {
                $givenData[$paramName] = $this->getContainerBind($paramName);

                continue;
            }

            /*
             * If there's an explicit parameter data for a variable
             * on the constructor, get and store it, then jump to the next parameter.
             */
            if ($this->hasGivenParameter($paramName)) {
                $givenData[$paramName] = $this->getParam($paramName);

                continue;
            }

            $type = (string) $param->getType();

            /*
             * If canAutoInject is enabled and the
             * required parameter is a class, get and store it in the $givenData[]
             */
            if ($this->container->canAutoInject() && class_exists($type)) {
                $givenData[$paramName] = (new self($this->getContainer()))->build($type);
            }
        }

        /*
         * Now we have all available data
         * to be used to construct a dependency.
         */
        return $givenData;
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
