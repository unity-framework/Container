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
    protected $constructorData = [];

    /**
     * DependencyBuilder constructor.
     *
     * @param IContainer $container
     * @param array      $constructorData
     */
    public function __construct(IContainer $container, $constructorData = null)
    {
        $this->container = $container;
        $this->constructorData = $constructorData;
    }

    /**
     * Constructs and returns a $class instance.
     *
     * @param $class
     *
     * @return object
     */
    public function build($class)
    {
        /* Gets a reflection class of $class */
        $reflectedClass = $this->reflectClass($class);

        /*
         * We need to check if $reflectedClass `hasParametersOnConstructor()`,
         * if it's true, then we `createInstanceWithParameters()`
         */
        if ($this->hasParametersOnConstructor($reflectedClass)) {
            return $this->createInstanceWithParameters($reflectedClass);
        }

        /*
        * If we're here, that means $reflectedClass has'nt a constructor,
        * so we just instantiate it...
        */
        return $this->createInstance($reflectedClass);
    }

    /**
     * Checks if a argument needed to construct a dependency was given.
     *
     * @param $paramName
     *
     * @return bool
     */
    protected function hasConstructorData($paramName)
    {
        return isset($this->constructorData[$paramName]);
    }

    /**
     * Gets a argument needed to construct a dependency.
     *
     * @param $paramName
     *
     * @return mixed
     */
    protected function getConstructorData($paramName)
    {
        return $this->constructorData[$paramName];
    }

    /**
     * Returns a ReflectionClass instance for $class.
     *
     * @param string $class Class to be reflected.
     *
     * @return ReflectionClass
     */
    protected function reflectClass($class)
    {
        return new ReflectionClass($class);
    }

    /**
     * Gets the constructor of the reflected class.
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionMethod
     */
    protected function getConstructor(ReflectionClass $reflectedClass)
    {
        return $reflectedClass->getConstructor();
    }

    /**
     * Checks if the reflected class has constructor.
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return bool
     */
    protected function hasConstructor(ReflectionClass $reflectedClass)
    {
        return !is_null($reflectedClass->getConstructor());
    }

    /**
     * Checks if the reflected class has arguments in the constructor.
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return bool
     */
    protected function hasParametersOnConstructor(ReflectionClass $reflectedClass)
    {
        if ($this->hasConstructor($reflectedClass)) {
            $constructor = $this->getConstructor($reflectedClass);

            return $constructor->getNumberOfParameters() > 0;
        }

        return false;
    }

    /**
     * Returns an array containing all parameters
     * required to construct the reflected class.
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return array
     */
    protected function getConstructorParameters(ReflectionClass $reflectedClass)
    {
        return $this->getConstructor($reflectedClass)->getParameters();
    }

    /**
     * Returns an array containing all available
     * data to construct the reflected class.
     *
     * @param $constructorParameters
     *
     * @return array
     */
    protected function getConstructorParametersData($constructorParameters)
    {
        $constructorData = [];

        /*
         * Here we check if we have data for each
         * each constructor argument
         */
        foreach ($constructorParameters as $constructorParameter) {
            $paramName = $constructorParameter->getName();

            /*
             * If there's an explicit argument data for a variable
             * on the constructor, get and store it, then jump to the next argument.
             */
            if ($this->hasConstructorData($paramName)) {
                $constructorData[$paramName] = $this->getConstructorData($paramName);

                continue;
            }

            $type = (string) $constructorParameter->getType();

            if ($this->container->hasBind($type)) {
                $constructorData[$paramName] = $this->container->getBind($type);

                continue;
            }

            /*
             * If canAutoInject is enabled and the
             * required argument is a class, get and store it in the $givenData[]
             */
            if ($this->container->canAutoInject() && class_exists($type)) {
                $constructorData[$paramName] = (new self($this->container))->build($type);
            }
        }

        /*
         * Now we have all available data
         * to be used to construct a dependency.
         */
        return $constructorData;
    }

    /**
     * Ensure that no needed argument is missing.
     *
     * @param $constructorParameters
     * @param $constructorData
     * @param $reflectedClass
     *
     * @throws MissingConstructorArgumentException
     */
    protected function ensureNoMissingConstructorParameter(
        $constructorParameters,
        $constructorData,
        $reflectedClass
    )
    {
        $reflectedClassName = $reflectedClass->getName();

        /*
         * We need to ensure that all required arguments to
         * construct the reflected class were given, for it,
         * we must check if the $constructorData contains
         * all $constructorParameters in your keys.
         */
        foreach ($constructorParameters as $constructorParameter) {
            $paramName = $constructorParameter->getName();

            /* Optional arguments should be ignored */
            if (!$constructorParameter->isOptional() && !array_key_exists(
                    $paramName,
                    $constructorData
                )) {
                throw new MissingConstructorArgumentException("Missing argument \${$paramName} for {$reflectedClassName}::__construct()");
            }
        }
    }

    /**
     * Puts all available data in the the reflected class
     * constructor instantiates and returns it.
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return object
     */
    protected function createInstanceWithParameters(ReflectionClass $reflectedClass)
    {
        $params = $this->getConstructorParameters($reflectedClass);
        $constructorData = $this->getConstructorParametersData($params);

        $this->ensureNoMissingConstructorParameter(
            $params,
            $constructorData, 
            $reflectedClass
        );

        return $reflectedClass->newInstanceArgs($constructorData);
    }

    /**
     * Creates a new instance of the reflected class
     * ignoring the constructor.
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return object
     */
    protected function createInstance(ReflectionClass $reflectedClass)
    {
        return $reflectedClass->newInstanceWithoutConstructor();
    }
}
