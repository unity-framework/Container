<?php

namespace Unity\Component\IoC;

use Unity\Component\IoC\Exceptions\ContainerException;

class Resolver
{
    /**
     * @var string $name Name of this resolver
     */
    protected $name;

    /**
     * @var mixed $entry The entry to be resolver
     */
    protected $entry;

    /**
     * @var mixed $singleton The Singleton instance
     */
    protected $singleton;

    function __construct($name, $entry)
    {
        $this->name = $name;
        $this->entry = $entry;
    }

    /**
     * Resolve this resolver
     *
     * @return object|string|mixed
     */
    function resolve()
    {
        return $this->getSingleton();
    }

    /**
     * Gets the name of this resolver
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * Gets the entry of the resolver
     * @return mixed
     */
    function getEntry()
    {
        return $this->entry;
    }

    /**
     * Checks if this resolver has a
     * singleton instance
     * @return bool
     */
    function hasSingleton()
    {
        return !is_null($this->singleton);
    }

    /**
     * Gets the resolver instance
     *
     * Creates the instance if does'nt exists
     * and return it.
     *
     * If the entry isn't a existing class or a callback
     * it's returns the entry value
     *
     * @return object|string|mixed
     * @throws ContainerException
     */
    function getSingleton()
    {
        if($this->hasSingleton())
            return $this->singleton;

        $entry = $this->getEntry();

        $instance = $this->make($entry);

        $this->setSingleton($instance);

        return $instance;
    }

    /**
     * Set the singleton instance
     *
     * @param $instance
     * @return mixed
     */
    function setSingleton($instance)
    {
        return $this->singleton = $instance;
    }

    /**
     * Makes a new instance of the entry if
     * the entry is a valid class or a Callable
     * that return an instance, otherwise, returns
     * the entry
     *
     * @param $entry
     * @return callable|mixed|null|object|string
     * @throws ContainerException
     */
    function make()
    {
        $instance = null;
        $entry = $this->getEntry();

        if (is_callable($entry))
            $instance = call_user_func($entry, $this);

        if(is_string($entry)) {
            try
            {
                $instance = InstanceBuilder::build($entry);
            }
            catch (\Exception $ex)
            {
                throw new ContainerException("An error occurs while trying to build \" {$this->name} \" dependencies.\nError: " . $ex->getMessage(), $ex->getCode());
            }
        }

        if(is_null($instance))
            $instance = $entry;

        return $instance;
    }
}