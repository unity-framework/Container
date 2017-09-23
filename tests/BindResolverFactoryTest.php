<?php

use PHPUnit\Framework\TestCase;
use Unity\Component\Container\Bind\BindResolver;
use Unity\Component\Container\Contracts\IContainer;
use Unity\Component\Container\Bind\BindResolverFactory;

/**
 * @author Eleandro Duzentos <eleandro@inbox.ru>
 */
class BindResolverFactoryTest extends TestCase
{
    public function testMake()
    {
        $containerMock = $this->createMock(IContainer::class);

        $this->assertInstanceOf(
            BindResolver::class,
            BindResolverFactory::make('testId', 'e200', $containerMock)
        );
    }
}
