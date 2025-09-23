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

use League\Container\Container as LeagueContainer;
use Psr\Container\ContainerInterface;
use Tobento\Service\Autowire\Test\AutowireWithParameters;

class AutowireWithParametersTest extends AutowireWithParameters
{
    protected function container(): ContainerInterface
    {
        return new LeagueContainer();
    }
}