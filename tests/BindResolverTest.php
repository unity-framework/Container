<?php

use Helpers\Mocks\TestBase;
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
}
