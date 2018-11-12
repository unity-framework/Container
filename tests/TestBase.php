<?php

namespace Unity\Tests\Container;

use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Contracts\Bind\IBindResolver;
use Unity\Component\Container\Contracts\Dependency\IDependencyFactory;
use Unity\Component\Container\Contracts\Dependency\IDependencyResolver;
use Unity\Component\Container\Contracts\Factories\IBindResolverFactory;
use Unity\Component\Container\Contracts\Factories\IDependencyResolverFactory;
use Unity\Component\Container\Contracts\IContainer;

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
