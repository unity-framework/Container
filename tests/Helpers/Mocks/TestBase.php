<?php

namespace Helpers\Mocks;

use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Container;
use Unity\Component\Container\Contracts\IBindResolverFactory;
use Unity\Component\Container\Contracts\IDependencyFactory;
use Unity\Component\Container\Contracts\IDependencyResolver;
use Unity\Component\Container\Contracts\IDependencyResolverFactory;

class TestBase extends TestCase
{
    public function mockContainer()
    {
        return $this->createMock(Container::class);
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
        $br = $this->createMock(IDependencyResolver::class);

        $br
            ->expects($this->any())
            ->method('resolve')
            ->willReturn(true);

        $br
            ->expects($this->any())
            ->method('make')
            ->willReturn(true);

        $brf = $this->createMock(IBindResolverFactory::class);

        $brf
            ->expects($this->any())
            ->method('make')
            ->willReturn($br);

        return $brf;
    }
}
