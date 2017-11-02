<?php

use Unity\Tests\Container\TestBase;
use Unity\Component\Container\Bind\BindResolver;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolverTest extends TestBase
{
    public function testResolve()
    {
        $bindResolver = new BindResolver(function () {
            return true;
        }, $this->mockContainer());

        $this->assertTrue($bindResolver->resolve());
    }

    public function testResolveWithContainer()
    {
        $containerMock = $this->mockContainer();

        $containerMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(true);

        $bindResolver = new BindResolver(function ($containerMock) {
            return $containerMock->get(null);
        }, $containerMock);

        $this->assertTrue($bindResolver->resolve());
    }
}
