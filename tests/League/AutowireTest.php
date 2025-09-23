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

use League\Container\Container as LeagueContainer;
use Psr\Container\ContainerInterface;
use Tobento\Service\Autowire\Test\AutowireBasics;

class AutowireTest extends AutowireBasics
{
    protected function container(): ContainerInterface
    {
        return new LeagueContainer();
    }
}