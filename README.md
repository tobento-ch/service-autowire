# Autowire Service

The Autowire Service provides autowiring for PSR-11 containers.

## Table of Contents

- [Getting started](#getting-started)
    - [Requirements](#requirements)
    - [Highlights](#highlights)
    - [Simple Example](#simple-example)
- [Documentation](#documentation)
    - [Resolve](#resolve)
    - [Call](#call)
- [Credits](#credits)
___

# Getting started

Add the latest version of the autowire service running this command.

```
composer require tobento/service-autowire
```

## Requirements

- PHP 8.0 or greater

## Highlights

- Framework-agnostic, will work with any project

## Simple Example

Here is a simple example of how to use the Autowire service.

```php
use Tobento\Service\Autowire\Autowire;

// Autowiring an object
$foo = (new Autowire($container))->resolve(Foo::class);

// Call method using autowiring
$value = (new Autowire($container))->call([Foo::class, 'method']);
```

# Documentation

## Resolve

Define any build-in parameters which are not resolvable, either by parameter name or position.

```php
use Tobento\Service\Autowire\Autowire;

// By name
$foo = (new Autowire($container))->resolve(Foo::class, ['name' => 'value']);

// By position
$foo = (new Autowire($container))->resolve(Foo::class, [2 => 'value']);
```

You might use a try/catch block:

```php
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireException;

try {
    $foo = (new Autowire($container))->resolve([Foo::class, 'method']);
} catch (AutowireException $e) {
    // not resolvable
}
```

## Call

Define any build-in parameters which are not resolvable, either by parameter name or position.

```php
use Tobento\Service\Autowire\Autowire;

// Using array callable
$value = (new Autowire($container))->call([Foo::class, 'method'], ['name' => 'value']);

// Using closure
$value = (new Autowire($container))->call(function(Foo $foo, $name) {
    return $name;
}, ['name' => 'value']);

var_dump($value); // string(5) "value"

// Using class with __invoke
$value = (new Autowire($container))->call(Invokable::class, ['name' => 'value']);

// Using Class::method syntax
$value = (new Autowire($container))->call('Foo::method', ['name' => 'value']);
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)