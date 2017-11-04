<?php

namespace Unity\Tests\Container;

use PHPUnit\Framework\TestCase;
use Unity\Contracts\Container\Bind\IBindResolver;
use Unity\Contracts\Container\Dependency\IDependencyFactory;
use Unity\Contracts\Container\Dependency\IDependencyResolver;
use Unity\Contracts\Container\Factories\IBindResolverFactory;
use Unity\Contracts\Container\Factories\IDependencyResolverFactory;
use Unity\Contracts\Container\IContainer;

class TestBase extends TestCase
{
    public function mockContainer()
    {
        return $this->createMock(IContainer::class);
    }

    public function mockDependencyFactory()
    {
        return $this->createMock(IDependencyFactory::class);
    }

    public function mockDependencyResolverFactory()
    {
        $df = $this->createMock(IDependencyResolver::class);

        $df
            ->method('resolve')
            ->willReturn(true);

        $df
            ->method('make')
            ->willReturn(true);

        $drf = $this->createMock(IDependencyResolverFactory::class);

        $drf
            ->method('make')
            ->willReturn($df);

        return $drf;
    }

    public function mockBindResolverFactory()
    {
        $brf = $this->createMock(IBindResolverFactory::class);

        $brf
            ->method('make')
            ->willReturn(true);

        return $brf;
    }

    public function mockBindResolver()
    {
        return $this->createMock(IBindResolver::class);
    }
}
