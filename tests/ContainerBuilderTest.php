<?php

use e200\MakeAccessible\Make;
use PHPUnit\Framework\TestCase;
use Unity\Component\Container\ContainerBuilder;
use Unity\Contracts\Container\IContainer;

class ContainerBuilderTest extends TestCase
{
    public function testAutoResolver()
    {
        $acb = $this->getAccessibleContainerBuilder();
        $cb = $acb->getInstance();

        $this->assertTrue($acb->autoResolve);

        $cb->autoResolve(false);
        $this->assertFalse($acb->autoResolve);
        $cb->autoResolve(true);
        $this->assertTrue($acb->autoResolve);
    }

    public function testCanUseAnnotations()
    {
        $acb = $this->getAccessibleContainerBuilder();
        $cb = $acb->getInstance();

        $this->assertFalse($acb->useAnnotations);

        $cb->canUseAnnotations(true);
        $this->assertTrue($acb->useAnnotations);
        $cb->canUseAnnotations(false);
        $this->assertFalse($acb->useAnnotations);
    }

    public function testBuild()
    {
        $this->assertInstanceOf(IContainer::class, (new ContainerBuilder())->build());
    }

    public function getAccessibleContainerBuilder()
    {
        return Make::accessible(new ContainerBuilder());
    }
}
