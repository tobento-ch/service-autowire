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

class WithUnionParameterAllowsNullNotFound
{
    public function __construct(
        protected null|Inexistence|NotFound $name,
    ) {}
    
    public function getName()
    {
        return $this->name;
    }    
}