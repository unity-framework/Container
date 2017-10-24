<?php

use e200\MakeAccessible\Make;
use PHPUnit\Framework\TestCase;
use Unity\Contracts\Container\IContainer;
use Unity\Component\Container\ContainerBuilder;

class ContainerBuilderTest extends TestCase
{
    function testAutoResolver()
    {
        $acb = $this->getAccessibleContainerBuilder();
        $cb = $acb->getInstance();
        
        $this->assertTrue($acb->autoResolve);

        $cb->autoResolve(false);
        $this->assertFalse($acb->autoResolve);
        $cb->autoResolve(true);
        $this->assertTrue($acb->autoResolve);
    }

    function testCanUseAnnotations()
    {
        $acb = $this->getAccessibleContainerBuilder();
        $cb = $acb->getInstance();
        
        $this->assertFalse($acb->useAnnotations);

        $cb->canUseAnnotations(true);
        $this->assertTrue($acb->useAnnotations);
        $cb->canUseAnnotations(false);
        $this->assertFalse($acb->useAnnotations);
    }
    
    function testBuild()
    {
        $this->assertInstanceOf(IContainer::class, (new ContainerBuilder())->build());
    }

    function getAccessibleContainerBuilder()
    {
        return Make::accessible(new ContainerBuilder());
    }
}