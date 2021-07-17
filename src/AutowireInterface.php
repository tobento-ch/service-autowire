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

namespace Tobento\Service\Autowire;

/**
 * AutowireInterface
 */
interface AutowireInterface
{    
    /**
     * Resolve the given class.
     *
     * @param string $class
     * @param array<int|string, mixed> $parameters
     *
     * @throws AutowireException
     *
     * @return object
     */
    public function resolve(string $class, array $parameters = []): object;

    /**
     * Resolve callable and calls it.
     *
     * @param mixed $callable
     * @param array<int|string, mixed> $parameters
     *
     * @throws AutowireException
     *
     * @return mixed The result of the callable.
     */    
    public function call(mixed $callable, array $parameters = []): mixed;   
}