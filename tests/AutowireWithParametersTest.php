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
 * AutowireWithParametersTest tests
 */
class AutowireWithParametersTest extends TestCase
{
    protected function autowire(): AutowireInterface
    {
        return new Autowire(new Container());
    }
    
    public function testThrowsAutowireExceptionIfNotResolvable()
    {
        $this->expectException(AutowireException::class);
        
        $this->autowire()->resolve(WithBuildInParameterAndClasses::class);
    }
    
    public function testWithNamedParamter()
    {        
        $this->assertInstanceOf(
            WithBuildInParameterAndClasses::class,
            $this->autowire()->resolve(
                WithBuildInParameterAndClasses::class,
                ['name' => 'hello']
            )
        );
    }
    
    public function testWithPositionParamter()
    {        
        $this->assertInstanceOf(
            WithBuildInParameterAndClasses::class,
            $this->autowire()->resolve(
                WithBuildInParameterAndClasses::class,
                [1 => 'hello']
            )
        );
    }    
}