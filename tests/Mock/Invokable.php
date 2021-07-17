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

class Invokable
{
    public function __construct(
        protected Foo $foo
    ) {}
    
    public function __invoke(
        Bar $bar
    ) {
        return 'invoked';
    }      
}