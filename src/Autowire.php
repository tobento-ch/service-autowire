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
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionNamedType;
use ReflectionUnionType;
use Closure;
use Throwable;

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
        } catch (Throwable $t) {
            throw new AutowireException(
                sprintf('Reflector class (%s) is not instantiable', $class),
                0,
                $t
            );
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
            $resolved[] = $this->resolveParameter($id, $parameter, $parameters);
        }

        return $resolved;
    }
    
    /**
     * Resolves the parameters.
     * 
     * @param string $id
     * @param ReflectionParameter $parameter
     * @param array<int|string, mixed> $parameters
     * @return mixed The resolved parameter.
     */
    private function resolveParameter(
        string $id,
        ReflectionParameter $parameter,
        array $parameters = []
    ): mixed {
        // Resolve by parameters.
        if ($this->hasMatchedParameter($parameter, $parameters))
        {
            return $this->getMatchedParameter($parameter, $parameters);
        }
        
        // Resolve by type.
        $type = $parameter->getType();

        if (
            $type instanceof ReflectionNamedType
            && !is_null($resolved = $this->resolveNamedType($type))
        ){
            return $resolved;
        }
        
        if ($type instanceof ReflectionUnionType)
        {
            foreach($type->getTypes() as $namedType)
            {
                if (!is_null($resolved = $this->resolveNamedType($namedType)))
                {
                    return $resolved;
                }
            }
        }

        // Handle optional parameters.
        if ($parameter->isDefaultValueAvailable() || $parameter->isOptional())
        {
            return $parameter->getDefaultValue();
        }
        
        // Lastly, check if parameters allows null.
        if ($parameter->allowsNull())
        {
            return null;
        }        
        
        throw new AutowireException(sprintf(
            'Parameter $%s of %s is not resolvable',
            $parameter->getName(),
            $id        
        ));
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
        if (
            $type->isBuiltin()
            || ! $this->container->has($type->getName())
        ) {
            return null;
        }

        return $this->container->get($type->getName());
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