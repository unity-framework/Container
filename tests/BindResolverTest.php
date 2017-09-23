<?php

use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Bind\BindResolver;
use Unity\Component\Container\Contracts\IContainer;

class BindResolverTest extends TestCase
{
    function testResolve()
    {
        $containerMock = $this->createMock(IContainer::class);

        $br = new BindResolver('testId', function ($container) use ($containerMock) {
            $this->assertSame($container, $containerMock);

            return ':)';
        }, $containerMock);

        $this->assertEquals(':)', $br->resolve());
    }
}
