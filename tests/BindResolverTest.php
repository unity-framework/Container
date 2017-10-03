<?php

use Unity\Component\Container\Bind\BindResolver;
use Helpers\Mocks\TestBase;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolverTest extends TestBase
{
    public function testResolve()
    {
        $bindResolver = new BindResolver(function () { return true; }, $this->mockContainer());

        $this->assertTrue($bindResolver->resolve());
    }
}
