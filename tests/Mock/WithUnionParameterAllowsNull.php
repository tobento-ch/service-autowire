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

class WithUnionParameterAllowsNull
{
    public function __construct(
        protected null|Inexistence|Foo|Bar $name,
    ) {}
    
    public function getName()
    {
        return $this->name;
    }    
}