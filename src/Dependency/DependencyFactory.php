<?php

namespace Unity\Component\Container\Dependency;

use ReflectionClass;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlockFactory;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Contracts\IDependencyFactory;
use Unity\Component\Container\Exceptions\ClassNotFoundException;
use Unity\Component\Container\Exceptions\NonInstantiableClassException;
use Unity\Reflector\Reflector;

/**
 * Class DependencyBuilder.
 *
 * Builds dependencies using Reflector.
 *
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class DependencyFactory implements IDependencyFactory
{
    protected $instance;

    /** @var IContainer */
    protected $container;
    /** @var Reflector */
    protected $reflector;

    /**
     * DependencyBuilder constructor.
     *
     * @param Reflector $reflector
     */
    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    /**
     * Sets the Container instance.
     *
     * Since this is a dependency of the container and the container
     * is a dependency of this, this is the best way i found to decouple
     * each other.
     *
     * @param IContainer $container
     */
    public function setContainer(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Makes a `$class` instance.
     *
     * @param string $class        Class name.
     * @param array  $dependencies Constructor dependencies.
     *
     * @throws NonInstantiableClassException
     *
     * @return mixed|object
     */
    public function make($class, $dependencies = [])
    {
        $refClass = $this->reflector::reflect($class);

        if (!$refClass->isInstantiable()) {
            throw new NonInstantiableClassException("Class \"{$class}\" cannot be instantiated.");
        }

        if ($this->reflector->hasRequiredParams($refClass)) {
            $dependencies = $this->getConstructorArgs($dependencies, $refClass);

            $instance = $refClass->newInstanceArgs($dependencies);
        } else {
            $instance = $refClass->newInstanceWithoutConstructor();
        }

        if ($this->container->canUseAnnotations()) {
            $this->injectPropertyDependencies($refClass, $instance);
        }

        return $instance;
    }

    /**
     * @param $dependencies
     * @param ReflectionClass $refClass
     *
     * @return array
     */
    protected function getConstructorArgs($dependencies, ReflectionClass $refClass)
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
            /*****************************************************************************
             * If there's an explicit value for `$param` on `$dependencies` we add it to *
             * $resolvedParams`.                                                         *
             *****************************************************************************/
            if (isset($dependencies[$key])) {
                $resolvedParams[$key] = $dependencies[$key];

                //////////////////////////////////////////////////////////////////////////
                // We already have its parameter resolved, there's nothing more to do. //
                //////////////////////////////////////////////////////////////////////////
                continue;
            }

            if ($param->hasType()) {
                $paramType = (string) $param->getType();

                if ($this->container->isBound($paramType)) {
                    $resolvedParams[$key] = $this->container->getBoundValue($paramType);

                    continue;
                }

                if ($this->container->canAutowiring() && class_exists($paramType)) {
                    $resolvedParams[$key] = $this->innerMake($paramType);
                }
            }
        }

        return $resolvedParams;
    }

    /**
     * Inject dependencies into properties based on their DocBlock.
     *
     * @param ReflectionClass $redClass
     * @param object          $instance
     */
    protected function injectPropertyDependencies($redClass, $instance)
    {
        $dcInstance = DocBlockFactory::createInstance();

        ////////////////////////////
        // Reading all properties //
        ////////////////////////////
        foreach ($redClass->getProperties() as $property) {
            $dc = $dcInstance->create($property);

            ////////////////////////////////
            // Has tag @inject and @var?? //
            ////////////////////////////////
            if ($dc->hasTag('inject') && $dc->hasTag('var')) {
                $varTags = $dc->getTagsByName('var');

                ///////////////////////////////////////////////////////////////////////////
                // DocBlock returns a collection of tags, its the last one that matters. //
                ///////////////////////////////////////////////////////////////////////////
                $tag = end($varTags);

                /*************************************************************************
                 * Tag value is the text next to the tag, e.g.: "@var \Unity\Support\Str"*
                 * where "Unity\Support\Str" is the value.                               *
                 ************************************************************************/
                $class = $this->getTagValue($tag);

                $classInstance = $this->innerMake($class);

                /****************************************************************************
                 * Make the instance accessible in case of a protected or private property. *
                 * Thats why this type of injection isn't recommend, because its breaks     *
                 * encapsulation.                                                           *
                 ****************************************************************************/
                $this->reflector->makeAccessibleIfInaccessible($property);

                ////////////////////////////////////////////////
                // Here we inject the value. And... Thats it. //
                ////////////////////////////////////////////////
                $property->setValue($instance, $classInstance);
            }
        }
    }

    /**
     * Gets a DockBlock tag value.
     *
     * @param Tag $tag
     *
     * @return string
     */
    protected function getTagValue(Tag $tag)
    {
        $exp = explode(' ', $tag->render());

        if (count($exp) != 2) {
            return false;
        }

        $value = end($exp);

        if (empty($value)) {
            return false;
        }

        return $value;
    }

    /**
     * Makes internal DependencyFactory instances.
     *
     * Theses instances are needed when we are autowiring our dependencies.
     *
     * @param string $class Class name.
     *
     * @return mixed|object
     *
     * @throws ClassNotFoundException
     */
    protected function innerMake($class)
    {
        if (!class_exists($class)) {
            throw new ClassNotFoundException("Class '{$class}' not found.");
        }

        $df = new self($this->reflector);

        $df->setContainer($this->container);

        $instance = $df->make($class);

        unset($df);

        return $instance;
    }
}
