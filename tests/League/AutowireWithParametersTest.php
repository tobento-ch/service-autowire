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

namespace Tobento\Service\Autowire\Test\League;

use Tobento\Service\Autowire\Test\AutowireWithParametersTest as BaseAutowireWithParametersTest;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireInterface;
use Tobento\Service\Autowire\AutowireException;
use Tobento\Service\Container\Container;
use League\Container\Container as LeagueContainer;
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
class AutowireWithParametersTest extends BaseAutowireWithParametersTest
{
    protected function autowire(): AutowireInterface
    {
        return new Autowire(new LeagueContainer());
    }
}