<?php

namespace Utopia\DI\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\DI\Container;
use Utopia\DI\Resource;

class ContainerTest extends TestCase
{
    protected ?Container $container = null;

    public function setUp(): void
    {
        $this->container = new Container();

        $user = new Resource();
        $user
            ->setName('user')
            ->inject('age')
            ->setCallback(fn (int $age) => 'John Doe is '.$age.' years old.')
        ;

        $age = new Resource();
        $age
            ->setName('age')
            ->setCallback(fn () => 25)
        ;

        $this->container
            ->set($user)
            ->set($age)
        ;
    }

    public function tearDown(): void
    {
        $this->container = null;
    }

    public function testResolution(): void
    {
        $this->assertSame('John Doe is 25 years old.', $this->container->get('user'));
    }

    public function testCanResolveResourcesWithDependenciesAndPerContextCache(): void
    {
        $counter = 0;

        $this->container
            ->setResource('counter', function () use (&$counter) {
                $counter++;

                return $counter;
            })
            ->setResource('message', fn (int $counter) => "counter-{$counter}", ['counter'])
        ;

        $this->assertSame(1, $this->container->getResource('counter', 'request-1'));
        $this->assertSame(1, $this->container->getResource('counter', 'request-1'));
        $this->assertSame('counter-1', $this->container->getResource('message', 'request-1'));

        $this->assertSame(2, $this->container->getResource('counter', 'request-2'));
        $this->assertSame('counter-2', $this->container->getResource('message', 'request-2'));
    }

    public function testContextSpecificDefinitionsOverrideDefaultContext(): void
    {
        $this->container
            ->setResource('greeting', fn () => 'hello from default')
            ->setResource('greeting', fn () => 'hello from request-1', context: 'request-1')
        ;

        $this->assertSame('hello from request-1', $this->container->getResource('greeting', 'request-1'));
        $this->assertSame('hello from default', $this->container->getResource('greeting', 'request-2'));
    }

    public function testCanResolveResourceListsAndContainerReference(): void
    {
        $this->container
            ->setResource('name', fn () => 'utopia')
            ->setResource(
                'summary',
                fn (string $name, Container $di) => "{$name}-".($di === $this->container ? 'same' : 'different'),
                ['name', 'di']
            )
        ;

        $resources = $this->container->getResources(['name', 'summary'], 'request-1');

        $this->assertSame('utopia', $resources['name']);
        $this->assertSame('utopia-same', $resources['summary']);
    }

    public function testCanRefreshAndPurgeContexts(): void
    {
        $counter = 0;

        $this->container
            ->setResource('counter', function () use (&$counter) {
                $counter++;

                return $counter;
            })
            ->setResource('request-id', fn () => 'request-1', context: 'request-1')
        ;

        $this->assertSame(1, $this->container->getResource('counter', 'request-1'));

        $this->container->refresh('counter', 'request-1');

        $this->assertSame(2, $this->container->getResource('counter', 'request-1'));
        $this->assertTrue($this->container->has('request-id', 'request-1'));

        $this->container->purge('request-1');

        $this->assertFalse($this->container->has('request-id', 'request-1'));
        $this->assertSame(3, $this->container->getResource('counter', 'request-1'));
    }

    public function testUpdatingDefaultContextInvalidatesCachedInstancesAcrossContexts(): void
    {
        $this->container->setResource('config', fn () => 'v1');

        $this->assertSame('v1', $this->container->getResource('config', 'request-1'));
        $this->assertSame('v1', $this->container->getResource('config', 'request-2'));

        $this->container->setResource('config', fn () => 'v2');

        $this->assertSame('v2', $this->container->getResource('config', 'request-1'));
        $this->assertSame('v2', $this->container->getResource('config', 'request-2'));
    }
}
