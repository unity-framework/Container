<?php

namespace Unity\Component\Container\Dependency;

use ReflectionClass;
use Unity\Component\Container\Contracts\Dependency\IDependencyFactory;
use Unity\Component\Container\Exceptions\ClassNotFoundException;
use Unity\Component\Container\Exceptions\NonInstantiableClassException;
use Unity\Reflector\Contracts\IReflector;

/**
 * Class DependencyBuilder.
 *
 * Builds dependencies using Reflector.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyFactory implements IDependencyFactory
{
    /** @var bool */
    protected $autoResolve = true;
    /** @var bool */
    protected $useAnnotations = false;

    /** @var IReflector */
    protected $reflector;

    /**
     * DependencyBuilder constructor.
     *
     * @param bool       $autoResolve    Tells if `$this` can auto resolve dependencies.
     * @param bool       $useAnnotations Tells if `$this` can auto resolve
     *                                   dependencies using annotations.
     * @param IReflector $reflector      Reflection helper contract.
     */
    public function __construct($autoResolve, $useAnnotations, IReflector $reflector)
    {
        $this->autoResolve = $autoResolve;
        $this->useAnnotations = $useAnnotations;
        $this->reflector = $reflector;
    }

    /**
     * Makes a `$class` instance.
     *
     * @param string $class     Class name.
     * @param array  $arguments Constructor arguments.
     * @param array  $binds
     *
     * @throws NonInstantiableClassException
     *
     * @return mixed|object
     */
    public function make($class, $arguments = [], $binds = [])
    {
        $refClass = $this->reflector->reflect($class);

        if (!$refClass->isInstantiable()) {
            throw new NonInstantiableClassException("Class \"{$class}\" cannot be instantiated.");
        }

        if ($this->reflector->hasRequiredParams($refClass)) {
            $arguments = $this->getConstructorArgs($refClass, $arguments, $binds);

            $instance = $refClass->newInstanceArgs($arguments);
        } else {
            $instance = $refClass->newInstance();
        }

        return $instance;
    }

    /**
     * @param ReflectionClass $refClass
     * @param array           $arguments
     * @param array           $binds
     *
     * @return array
     */
    protected function getConstructorArgs(ReflectionClass $refClass, $arguments, $binds)
    {
        /////////////////////////////////////////////////////////
        // Here we'll store each matched constructor parameter //
        /////////////////////////////////////////////////////////
        $resolvedParams = [];

        /////////////////////////////////
        // All constructor parameters. //
        /////////////////////////////////
        $params = $this->reflector->getConstructorParameters($refClass);

        foreach ($params as $key => $param) {
            /**************************************************************************
             * If there's an explicit value for `$param` on `$arguments` we add it to *
             * `$resolvedParams`.                                                     *
             **************************************************************************/
            if (array_key_exists($key, $arguments)) {
                $resolvedParams[$key] = $arguments[$key];

                /////////////////////////////////////////////////////////////////////////
                // We already have its parameter resolved, there's nothing more to do. //
                /////////////////////////////////////////////////////////////////////////
                continue;
            }

            if ($param->hasType()) {
                $paramType = (string) $param->getType();

                /**********************************************************************
                 * If there's an `IBindResolver` instance bound to this `$paramType`  *
                 * we call the `IBindResolver::resolve()` and add the return value to *
                 * `$resolvedParams`.                                                 *
                 **********************************************************************/
                if (array_key_exists($paramType, $binds) && interface_exists($paramType)) {
                    $resolvedParams[$key] = $binds[$paramType]->resolve();

                    //////////////////////////////////////////////////////////////////////////
                    // We already have its parameter resolved, there's nothing more to do. //
                    //////////////////////////////////////////////////////////////////////////
                    continue;
                }

                if ($this->autoResolve && class_exists($paramType)) {
                    $resolvedParams[$key] = $this->innerMake($paramType);
                }
            }
        }

        return $resolvedParams;
    }

    /**
     * Makes internal DependencyFactory instances.
     *
     * Theses instances are needed when we are autowiring our dependencies.
     *
     * @param string $class Class name.
     *
     * @throws ClassNotFoundException
     *
     * @return mixed|object
     */
    protected function innerMake($class)
    {
        if (!class_exists($class)) {
            throw new ClassNotFoundException("Class '{$class}' not found.");
        }

        $df = new self(
            $this->autoResolve,
            $this->useAnnotations,
            $this->reflector
        );

        $instance = $df->make($class);

        unset($df);

        return $instance;
    }
}
