[![CI](https://github.com/utopia-php/di/actions/workflows/ci.yml/badge.svg)](https://github.com/utopia-php/di/actions/workflows/ci.yml)
![Total Downloads](https://img.shields.io/packagist/dt/utopia-php/di.svg)
[![Discord](https://img.shields.io/discord/564160730845151244?label=discord)](https://discord.gg/GSeTUeA)

Utopia DI is a minimal [PSR-11](https://www.php-fig.org/psr/psr-11/) compatible dependency injection container with parent-child scopes. It is designed to stay small while covering the dependency lifecycle used across the Utopia libraries. This library is maintained by the [Appwrite team](https://appwrite.io).

Although this library is part of the Utopia project, it is lightweight and works as a standalone package in any PHP codebase.

## Features

- PSR-11 compatible container interface
- Lazy factory execution with per-container result caching
- Child scopes that inherit parent definitions
- Local overrides inside a child scope without mutating the parent
- `Dependency` helper for name-based injection into callbacks

## Getting Started

Install using Composer:

```bash
composer require utopia-php/di
```

```php
require_once __DIR__.'/../vendor/autoload.php';

use Psr\Container\ContainerInterface;
use Utopia\DI\Container;
use Utopia\DI\Dependency;

$di = new Container();

$di->set(
    key: 'age',
    factory: new Dependency(
        injections: [],
        callback: fn () => 25
    )
);

$di->set(
    key: 'john',
    factory: new Dependency(
        injections: ['age'],
        callback: fn (int $age) => 'John Doe is '.$age.' years old.'
    )
);

$john = $di->get('john');
```

For `Dependency` factories, the `injections` array is matched to callback parameter names. The array order does not need to match the callback signature.

You can also register plain factories directly when you want full access to the container instance.

```php
$di->set(
    key: 'config',
    factory: fn (ContainerInterface $container) => [
        'dsn' => 'mysql:host=localhost;dbname=app',
        'username' => 'root',
        'password' => 'secret',
    ]
);

$request = $di->scope();

$request->set(
    key: 'db',
    factory: fn (ContainerInterface $container) => new PDO(
        $container->get('config')['dsn'],
        $container->get('config')['username'],
        $container->get('config')['password']
    )
);
```

## Resolution And Scopes

Factories are resolved once per container instance. Resolved values, including `null`, are cached after the first successful lookup.

A child scope behaves in two distinct ways:

- If the child does not define a key, it falls back to the parent and reuses the parent's resolved value.
- If the child defines the same key locally, it resolves and caches its own value without changing the parent.

```php
$counter = 0;

$di->set('requestId', function () use (&$counter): string {
    $counter++;

    return 'request-'.$counter;
});

$di->get('requestId'); // "request-1"

$child = $di->scope();

$child->get('requestId'); // "request-1" (falls back to the parent cache)

$child->set('requestId', function () use (&$counter): string {
    $counter++;

    return 'request-'.$counter;
});

$child->get('requestId'); // "request-2" (child now uses its own local definition)
$di->get('requestId'); // "request-1" (parent is unchanged)
```

## Error Behavior

- `get()` throws `Utopia\DI\Exceptions\NotFoundException` when a dependency does not exist in the current container or any parent scope.
- Factory failures are wrapped in `Utopia\DI\Exceptions\ContainerException`.
- Circular dependency resolution also throws `Utopia\DI\Exceptions\ContainerException`.

## System Requirements

Utopia DI requires PHP 8.2 or later. We recommend using the latest PHP version whenever possible.

## Development

Install dependencies and run the local checks:

```bash
composer install
composer test
composer analyze
composer format:check
composer refactor:check
```

`composer refactor:check` requires Rector dependencies from `tools/rector`:

```bash
composer install -d tools/rector
```

## More from Utopia

Our ecosystem contains small PHP packages focused on solving a single problem well.

You can browse the wider set of libraries in the [Utopia GitHub organization](https://github.com/utopia-php).

## Contributing

All code contributions, including those from maintainers, go through pull request review before merging.

Fork the project, create a feature branch from `main`, and open a pull request.

See the [Contributing Guide](CONTRIBUTING.md) for the expected workflow and local checks.

For security issues, email `security@appwrite.io` instead of opening a public issue.

## Copyright and license

The MIT License (MIT) [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
