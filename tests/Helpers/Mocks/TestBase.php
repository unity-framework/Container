<?php

namespace Helpers\Mocks;

use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Container;
use Unity\Component\Container\Dependency\DependencyFactory;

class TestBase extends TestCase
{
    public function mockContainer()
    {
        return $this->createMock(Container::class);
    }

    public function mockDependencyFactory()
    {
        return $this->createMock(DependencyFactory::class);
    }
}
