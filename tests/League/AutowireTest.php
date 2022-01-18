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

namespace Tobento\Service\Autowire\Test\Leaque;

use Tobento\Service\Autowire\Test\AutowireTest as BaseAutowireTest;
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
    WithUnionParameterAllowsNullNotFound,
    WithUnionDateTimeZone
};
use stdClass;

/**
 * AutowireTest tests
 */
class AutowireTest extends BaseAutowireTest
{
    protected function autowire(): AutowireInterface
    {
        return new Autowire(new LeagueContainer());
    }
}