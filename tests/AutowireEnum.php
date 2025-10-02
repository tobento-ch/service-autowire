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

namespace Tobento\Service\Autowire\Test;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireInterface;
use Tobento\Service\Autowire\AutowireException;
use Tobento\Service\Container\Container;

abstract class AutowireEnum extends TestCase
{
    abstract protected function container(): ContainerInterface;
    
    protected function autowire(): AutowireInterface
    {
        return new Autowire($this->container());
    }
    
    public function testWithEnumParameterRequired()
    {        
        $this->assertSame(
            Status::Pending,
            $this->autowire()->resolve(StatusWithEnumParameterRequired::class)->status()
        );
        
        $this->assertSame(
            Status::Running,
            $this->autowire()->resolve(StatusWithEnumParameterRequired::class, [Status::Running])->status()
        );        
    }
    
    public function testWithEnumParameterWithDefaultValue()
    {        
        $this->assertSame(
            Status::Running,
            $this->autowire()->resolve(StatusWithEnumParameterDefaultValue::class)->status()
        );
        
        $this->assertSame(
            Status::Pending,
            $this->autowire()->resolve(StatusWithEnumParameterDefaultValue::class, [Status::Pending])->status()
        );        
    }
    
    public function testWithEnumParameterOptional()
    {        
        $this->assertSame(
            null,
            $this->autowire()->resolve(StatusWithEnumParameterOptional::class)->status()
        );
        
        $this->assertSame(
            Status::Pending,
            $this->autowire()->resolve(StatusWithEnumParameterOptional::class, [Status::Pending])->status()
        );        
    }    
}

enum Status
{
    case Pending;
    case Running;
}

class StatusWithEnumParameterRequired
{
    public function __construct(
        private Status $status,
    ) {}
    
    public function status(): Status
    {
        return $this->status;
    }
}

class StatusWithEnumParameterDefaultValue
{
    public function __construct(
        private Status $status = Status::Running,
    ) {}
    
    public function status(): Status
    {
        return $this->status;
    }
}

class StatusWithEnumParameterOptional
{
    public function __construct(
        private null|Status $status = null,
    ) {}
    
    public function status(): null|Status
    {
        return $this->status;
    }
}