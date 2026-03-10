<p>
    <img height="45" src="docs/logo.png" alt="Logo">
</p>

[![Build Status](https://travis-ci.org/utopia-php/http.svg?branch=master)](https://travis-ci.org/utopia-php/http)
![Total Downloads](https://img.shields.io/packagist/dt/utopia-php/http.svg)
[![Discord](https://img.shields.io/discord/564160730845151244?label=discord)](https://discord.gg/GSeTUeA)

Utopia DI is a small dependency injection container with scoped resources. It is designed to stay simple while still covering the resource lifecycle used across the Utopia libraries. This library is maintained by the [Appwrite team](https://appwrite.io).

Although this library is part of the Utopia Framework project it is dependency free, and can be used as standalone with any other PHP project or framework.

## Getting Started

Install using Composer:

```bash
composer require utopia-php/di
```

```php
require_once __DIR__.'/../vendor/autoload.php';

use Utopia\DI\Container;

$di = new Container();

$di->setResource('config', fn () => ['region' => 'eu-west-1']);
$di->setResource('db', fn (array $config) => new PDO('dsn', 'username', 'password'), ['config']);

$db = $di->getResource('db', 'request-1');
```

Resources are cached per context. Definitions registered in the default `utopia` context are automatically available in other contexts, while context-specific definitions override the default for that request.

```php
$di->setResource('request-id', fn () => 'req-1', context: 'request-1');

$requestResources = $di->getResources(['db', 'request-id'], 'request-1');

$di->purge('request-1');
```

## System Requirements

Utopia DI requires PHP 8.2 or later. We recommend using the latest PHP version whenever possible.

## More from Utopia

Our ecosystem supports other thin PHP projects aiming to extend the core PHP Utopia libraries.

Each project is focused on solving a single, very simple problem and you can use composer to include any of them in your next project.

You can find all libraries in [GitHub Utopia organization](https://github.com/utopia-php).

## Contributing

All code contributions - including those of people having commit access - must go through a pull request and approved by a core developer before being merged. This is to ensure proper review of all the code.

Fork the project, create a feature branch, and send us a pull request.

You can refer to the [Contributing Guide](https://github.com/utopia-php/di/blob/master/CONTRIBUTING.md) for more info.

For security issues, please email security@appwrite.io instead of posting a public issue in GitHub.

## Copyright and license

The MIT License (MIT) [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
