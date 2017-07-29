<?php

namespace Unity\Component\IoC;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Unity\Component\IoC\Exceptions\ContainerException;
use Unity\Component\IoC\Exceptions\DuplicateResolverNameException;
use Unity\Component\IoC\Exceptions\NotFoundException;
use Unity\Helpers\Str;

class Container implements ContainerInterface
{
    /**
     * Registered resolvers collection
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * @var bool $autowiring Set if the Container
     * can or not inject dependencies on @Injectable classes
     */
    protected $autowiring;

    function __construct($autowiring = true)
    {
        $this->autowiring = $autowiring;

        Str::contains('@', '@');
    }

    /**
     * Finds an entry of the container by its identifier and returns the resolved entry.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface No resolver with name **this** was found on the container.
     * @throws ContainerExceptionInterface Error while trying to build **this** dependencies.
     *
     * @return mixed Entry.
     */
    function get($id)
    {
        if(isset($this->resolvers[$id])) {
            $value = $this->resolvers[$id]['entry'];

            if (is_callable($value))
                return $value($this);

            if($this->autowiring && is_string($value))
                try {
                if(is_null($this->resolvers[$id]['resolvedEntry']))
                    $this->resolvers[$id]['resolvedEntry'] = InstanceBuilder::build($value);

                    if (!is_null($this->resolvers[$id]['resolvedEntry']))
                        return $this->resolvers[$id]['resolvedEntry'];
                } catch (\Exception $ex) {
                    throw new ContainerException("An error occurs while trying to build \"${id}\" dependencies");
                }

                return $value;
        }

        throw new NotFoundException("No resolver with name \"${id}\" was found on the container.");
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    function has($id)
    {
        return isset($this->resolvers[$id]);
    }

    /**
     * Register a resolver
     *
     * @param string $id
     * @param \Closure|string $entry Identifier of the entry to register.
     * @throws DuplicateResolverNameException
     */
    function register($id, $entry)
    {
        if(isset($this->resolvers[$id]))
            throw new DuplicateResolverNameException("There's already a resolver with name \"${id}\" on the container");

        $this->resolvers[$id] = [
            'entry' => $entry,
            'resolvedEntry' => null
        ];
    }

    /**
     * Defines if autowiring is enabled or not
     *
     * @param bool $enabled
     */
    function autoWiring($enabled = true)
    {
        $this->autowiring = $enabled;
    }
}