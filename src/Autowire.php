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

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionNamedType;
use ReflectionUnionType;
use Closure;

/**
 * Autowire
 */
class Autowire implements AutowireInterface
{
    /**
     * Create a new Autowiring.
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        protected ContainerInterface $container
    ) {}
    
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
    public function resolve(string $class, array $parameters = []): object
    {
        if (!class_exists($class))
        {
            throw new AutowireException(
                sprintf('Class (%s) not found', $class)
            );
        }
        
        $reflectionClass = new ReflectionClass($class);
        
        try {
            $constructor = $reflectionClass->getConstructor();
            
            // If no parameters, just create and return new instance.
            if ($constructor === null) {
                return $reflectionClass->newInstance();
            }

            // Otherwise, we resolve the parameters.
            return $reflectionClass->newInstanceArgs(
                $this->resolveParameters($class, $constructor, $parameters)
            );
        } catch (AutowireException $t) {
            throw $t;
        }
    }

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
    public function call(mixed $callable, array $parameters = []): mixed
    {
        if ($callable instanceof Closure)
        {            
            $resolvedParameters = $this->resolveParameters(
                'callable',
                new ReflectionFunction($callable),
                $parameters
            );
            
            return call_user_func_array($callable, $resolvedParameters);
        }
        
        if (is_string($callable))
        {
            if (str_contains($callable, '::')) {
                $callable = explode('::', $callable, 2);
            } else {
                $callable = [$callable];
            }
        }
        
        if (is_object($callable) && is_callable($callable))
        {
            $callable = [$callable];
        }
        
        if (is_array($callable))
        {
            // resolve class
            if (isset($callable[0]) && is_string($callable[0]))
            {
                $callable[0] = $this->resolve($callable[0]);
            }
            
            // resolve object method.
            if (isset($callable[0]) && is_object($callable[0]))
            {
                $reflectionClass = new ReflectionClass($callable[0]::class);
                
                // we assume class is invokable.
                $callable[1] ??= '__invoke';
                
                if (
                    !is_string($callable[1])
                    || ! $reflectionClass->hasMethod($callable[1])
                ) {
                    throw new AutowireException(sprintf(
                        'Cannot call invalid or not found method %s::%s()',
                        $callable[0]::class,
                        is_string($callable[1]) ? $callable[1] : ''
                    ));
                }
                
                $reflectionMethod = $reflectionClass->getMethod($callable[1]);
                
                if (! $reflectionMethod->isPublic())
                {
                    throw new AutowireException(sprintf(
                        'Cannot call none public method %s::%s()',
                        $callable[0]::class,
                        $callable[1]
                    ));
                }
                
                $resolvedParameters = $this->resolveParameters(
                    $callable[0]::class.'::'.$callable[1].'()',
                    $reflectionMethod,
                    $parameters
                );
                
                $callable = array_slice($callable, 0, 2);

                if (! is_callable($callable))
                {
                    throw new AutowireException(
                        sprintf('Invalid callable for %s', $callable[0]::class)
                    );
                }
                
                return call_user_func_array($callable, $resolvedParameters);
            }
        }
        
        if (is_callable($callable))
        {
            return call_user_func_array($callable, $parameters);
        }
        
        throw new AutowireException('Invalid callable provided');
    }
    
    /**
     * Returns the container.
     *
     * @return ContainerInterface
     */
    public function container(): ContainerInterface
    {
        return $this->container;
    }
    
    /**
     * Resolves the parameters.
     * 
     * @param string $id
     * @param null|ReflectionFunctionAbstract $function
     * @param array<int|string, mixed> $parameters
     * @return array<mixed> The resolved parameters.
     */
    private function resolveParameters(
        string $id,
        null|ReflectionFunctionAbstract $function = null,
        array $parameters = []
    ): array {
        
        if (is_null($function))
        {
            return [];
        }
        
        $resolved = [];
            
        foreach($function->getParameters() as $parameter)
        {
            // Resolve by parameters.
            if ($this->hasMatchedParameter($parameter, $parameters))
            {
                $resolved[] = $this->getMatchedParameter($parameter, $parameters);
                continue;
            }
            
            // Resolve by type.
            $type = $parameter->getType();

            if (
                $type instanceof ReflectionNamedType
                && !is_null($solved = $this->resolveNamedType($type))
            ){
                $resolved[] = $solved;
                continue;
            }

            if ($type instanceof ReflectionUnionType)
            {
                foreach($type->getTypes() as $namedType)
                {
                    if (!is_null($solved = $this->resolveNamedType($namedType)))
                    {
                        $resolved[] = $solved;
                        continue 2;
                    }
                }
            }
            
            // Handle optional parameters.
            if ($parameter->isDefaultValueAvailable())
            {
                $resolved[] = $parameter->getDefaultValue();
                continue;
            }

            // Check if parameters allows null.
            if ($parameter->allowsNull())
            {
                $resolved[] = null;
                continue;
            }

            // Lastly, check if variadic parameter.
            if ($parameter->isVariadic())
            {
                continue;
            }        

            throw new AutowireException(sprintf(
                'Parameter $%s of %s is not resolvable',
                $parameter->getName(),
                $id        
            ));
        }

        return $resolved;
    }

    /**
     * Resolves the named type from the container.
     *
     * @param ReflectionNamedType $type
     * @return mixed The resolved value, otherwise null
     */
    private function resolveNamedType(ReflectionNamedType $type): mixed
    {
        // A built-in type is any type that is not a class, interface, or trait.
        // We do not resolve from container.
        if ($type->isBuiltin()) {
            return null;
        }
        
        try {
            if ($this->container->has($type->getName())) {
                return $this->container->get($type->getName());
            }
            
            return $this->resolve($type->getName());
        } catch (AutowireException $e) {
            return null;
        } catch (ContainerExceptionInterface $e) {
            return null;
        }
    }
    
    /**
     * If any paramter matches the given ReflectionParameter.
     *
     * @param ReflectionParameter $parameter
     * @param array<int|string, mixed> $parameters
     * @return bool True on match, otherwise false.
     */
    private function hasMatchedParameter(ReflectionParameter $parameter, array $parameters): bool
    {
        if (empty($parameters)) {
            return false;
        }

        if (array_key_exists($parameter->getName(), $parameters)) {
            return true;
        }
            
        if (array_key_exists($parameter->getPosition(), $parameters)) {
            return true;
        }
        
        return false;
    }

    /**
     * If any paramter matches the given ReflectionParameter.
     *
     * @param ReflectionParameter $parameter
     * @param array<int|string, mixed> $parameters
     * @return mixed The value.
     */
    private function getMatchedParameter(ReflectionParameter $parameter, array $parameters): mixed
    {
        return $parameters[$parameter->getName()] ?? $parameters[$parameter->getPosition()] ?? null;
    }    
}