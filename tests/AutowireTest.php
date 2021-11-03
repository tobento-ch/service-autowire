<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Autowire\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireInterface;
use Tobento\Service\Autowire\AutowireException;
use Tobento\Service\Container\Container;
use Tobento\Service\Autowire\Test\Mock\{
    Foo,
    Bar,
    Baz,
    FooInterface,
    WithBuildInParameter,
    WithBuildInParameterOptional,
    WithBuildInParameterAllowsNull,
    WithBuildInParameterAndClasses,
    WithParameter,
    WithParameters,
    WithoutParameters,
    WithUnionParameter,
    WithUnionParameterAllowsNull,
    WithUnionParameterAllowsNullNotFound
};
use stdClass;

/**
 * AutowireTest tests
 */
class AutowireTest extends TestCase
{
    protected function autowire(): AutowireInterface
    {
        return new Autowire(new Container());
    }
    
    public function testThrowsAutowireExceptionIfNotResolvable()
    {
        $this->expectException(AutowireException::class);
        
        $this->autowire()->resolve('invalid');
    }
    
    public function testThrowsAutowireExceptionIfClassNotExists()
    {
        $this->expectException(AutowireException::class);
        
        $this->autowire()->resolve(Inexistence::class);
    }
    
    public function testThrowsAutowireExceptionIfInterfaceIsGiven()
    {
        $this->expectException(AutowireException::class);
        
        $this->autowire()->resolve(FooInterface::class);
    }    
    
    public function testWithClassName()
    {        
        $this->assertInstanceOf(
            'stdClass',
            $this->autowire()->resolve('stdClass')
        );
    }
    
    public function testReturnsNewInstances()
    {
        $a = $this->autowire()->resolve('stdClass');
        $b = $this->autowire()->resolve('stdClass');
        
        $this->assertFalse($a === $b);
    }
    
    public function testThrowsAutowireExceptionIfParameterIsNotResolvable()
    {
        $this->expectException(AutowireException::class);
        
        $this->autowire()->resolve(WithBuildInParameter::class);
    }
    
    public function testWithBuildInParameterOptional()
    {        
        $this->assertInstanceOf(
            WithBuildInParameterOptional::class,
            $this->autowire()->resolve(WithBuildInParameterOptional::class)
        );
    }
    
    public function testWithBuildInParameterAllowsNull()
    {        
        $this->assertInstanceOf(
            WithBuildInParameterAllowsNull::class,
            $this->autowire()->resolve(WithBuildInParameterAllowsNull::class)
        );
    }

    public function testWithoutParameters()
    {        
        $this->assertInstanceOf(
            WithoutParameters::class,
            $this->autowire()->resolve(WithoutParameters::class)
        );
    }

    public function testWithParameter()
    {        
        $this->assertInstanceOf(
            WithParameter::class,
            $this->autowire()->resolve(WithParameter::class)
        );
    } 
    
    public function testWithParameters()
    {        
        $this->assertInstanceOf(
            WithParameters::class,
            $this->autowire()->resolve(WithParameters::class)
        );
    }
    
    public function testWithUnionParameterResolvesFirstFound()
    {
        $resolved = $this->autowire()->resolve(WithUnionParameter::class);
        
        $this->assertInstanceOf(
            Foo::class,
            $resolved->getName()
        );
    }
    
    public function testWithUnionParameterResolvesFirstFoundIfAllowsNull()
    {
        $resolved = $this->autowire()->resolve(WithUnionParameterAllowsNull::class);
        
        $this->assertInstanceOf(
            Foo::class,
            $resolved->getName()
        );
    }
    
    public function testWithUnionParameterAllowsNullAddsNullIfNotFound()
    {
        $resolved = $this->autowire()->resolve(WithUnionParameterAllowsNullNotFound::class);
        
        $this->assertSame(
            null,
            $resolved->getName()
        );
    }
    
    public function testContainerMethod()
    {
        $container = new Container();
        $autowire = new Autowire($container);
        
        $this->assertSame(
            $container,
            $autowire->container()
        );
    }    
}