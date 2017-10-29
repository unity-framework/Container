<?php

namespace Helpers\Mocks;

use PHPUnit\Framework\TestCase;
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
            ->expects($this->any())
            ->method('resolve')
            ->willReturn(true);

        $df
            ->expects($this->any())
            ->method('make')
            ->willReturn(true);

        $drf = $this->createMock(IDependencyResolverFactory::class);

        $drf
            ->expects($this->any())
            ->method('make')
            ->willReturn($df);

        return $drf;
    }

    public function mockBindResolverFactory()
    {
        $brf = $this->createMock(IBindResolverFactory::class);

        $brf
        ->expects($this->any())
        ->method('make')
        ->willReturn(true);

        return $brf;
    }
}
