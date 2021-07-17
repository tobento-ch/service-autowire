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

namespace Tobento\Service\Autowire\Test\Mock;

class Methods
{
    public function __construct(
        protected Baz $baz
    ) {}
    
    public function withoutParameters(): string
    {
        return 'withoutParameters';
    }
    
    public function withBuildInParameter(string $name): string
    {
        return $name;
    }
    
    public function withBuildInParameterAllowsNull(null|string $name): null|string
    {
        return $name;
    }
    
    public function withBuildInParameterOptional(null|string $name = null): null|string
    {
        return $name;
    }
    
    public function withParameter(Foo $foo): Foo
    {
        return $foo;
    }
    
    public function withParameters(Foo $foo, Bar $bar): string
    {
        return 'called';
    }
    
    public function withUnionParameter(Inexistence|Foo|Bar $name): mixed
    {
        return $name;
    }
    
    public function withUnionParameterAllowsNull(null|Inexistence|Foo|Bar $name): mixed
    {
        return $name;
    }
    
    public function withUnionParameterAllowsNullNotFound(null|Inexistence|NotFound $name): mixed
    {
        return $name;
    }     
    
    public function withBuildInParameterAndClasses(
        Foo $foo,
        string $name,
        Bar $bar
    ): string {
        return $name;
    }    
    
    private function withPrivateMethod(string $name = 'index')
    {
        return $name;
    }
}