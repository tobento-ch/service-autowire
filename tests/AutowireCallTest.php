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
    Methods,
    Invokable,
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
 * AutowireCallTest tests
 */
class AutowireCallTest extends TestCase
{
    protected function autowire(): AutowireInterface
    {
        return new Autowire(new Container());
    }
    
    public function testThrowsAutowireExceptionIfNotCallable()
    {
        $this->expectException(AutowireException::class);
        
        $this->autowire()->call('invalid');
    }
 
    public function testStringClassMethodSyntax()
    {
        $this->assertSame(
            'withoutParameters',
            $this->autowire()->call(
                'Tobento\Service\Autowire\Test\Mock\Methods::withoutParameters'
            )
        );
    }
    
    public function testClosure()
    {
        $this->assertSame(
            'hellow',
            $this->autowire()->call(
                function() {
                    return 'hellow';
                }
            )
        );
    }
    
    public function testClassGetsInvoked()
    {
        $this->assertSame(
            'invoked',
            $this->autowire()->call(
                Invokable::class
            )
        );
    }
    
    public function testClassObjectGetsInvoked()
    {
        $this->assertSame(
            'invoked',
            $this->autowire()->call(
                new Invokable(new Foo())
            )
        );
    }    
    
    public function testClosureWithParametersGetsAutowired()
    {
        $this->assertInstanceof(
            Foo::class,
            $this->autowire()->call(
                function(Foo $foo) {
                    return $foo;
                }
            )
        );
    }
    
    public function testClosureWithParametersUsesSetParamaters()
    {
        $foo = new Foo();
        
        $this->assertSame(
            $foo,
            $this->autowire()->call(
                function(Foo $foo) {
                    return $foo;
                },
                [$foo]
            )
        );
    }
    
    public function testArrayWithClassInstance()
    {
        $this->assertSame(
            'withoutParameters',
            $this->autowire()->call(
                [new Methods(new Baz()), 'withoutParameters']
            )
        );
    }
    
    public function testArrayWithClassName()
    {
        $this->assertSame(
            'withoutParameters',
            $this->autowire()->call(
                [Methods::class, 'withoutParameters']
            )
        );
    }
    
    public function testThrowsAutowireExceptionIfParameterIsNotResolvable()
    {
        $this->expectException(AutowireException::class);

        $this->autowire()->call(
            [Methods::class, 'withBuildInParameter']
        );
    }
    
    public function testThrowsAutowireExceptionIfMethodIsPrivate()
    {
        $this->expectException(AutowireException::class);

        $this->autowire()->call(
            [Methods::class, 'withPrivateMethod']
        );
    }    
    
    public function testWithBuildInParameter()
    {        
        $this->assertSame(
            'welcome',
            $this->autowire()->call(
                [Methods::class, 'withBuildInParameter'],
                ['welcome']
            )
        );
    }
    
    public function testWithBuildInParameterAllowsNull()
    {        
        $this->assertSame(
            null,
            $this->autowire()->call(
                [Methods::class, 'withBuildInParameterAllowsNull'],
            )
        );
    } 
    
    public function testWithBuildInParameterAllowsNullButUsesParam()
    {        
        $this->assertSame(
            'welcome',
            $this->autowire()->call(
                [Methods::class, 'withBuildInParameterAllowsNull'],
                ['welcome']
            )
        );
    }
    
    public function testWithBuildInParameterOptional()
    {        
        $this->assertSame(
            null,
            $this->autowire()->call(
                [Methods::class, 'withBuildInParameterOptional']
            )
        );
    }
    
    public function testWithBuildInParameterOptionalButUsesParam()
    {        
        $this->assertSame(
            'welcome',
            $this->autowire()->call(
                [Methods::class, 'withBuildInParameterOptional'],
                ['welcome']
            )
        );
    }
 
    public function testWithParameter()
    {
        $foo = new Foo();
        
        $this->assertSame(
            $foo,
            $this->autowire()->call(
                [Methods::class, 'withParameter'],
                [$foo]
            )
        );
    } 
    
    public function testWithParameterGetsAutowired()
    {        
        $this->assertInstanceof(
            Foo::class,
            $this->autowire()->call(
                [Methods::class, 'withParameter']
            )
        );
    } 
}