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

class WithSplatParameter
{
    /**
     * @var array
     */
    protected array $foos = []; 
    
    public function __construct(
        FooInterface ...$foo
    ) {
        $this->foos = $foo;
    }
}