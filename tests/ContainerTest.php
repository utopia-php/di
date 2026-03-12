<?php

declare(strict_types=1);

namespace Utopia\DI\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Utopia\DI\Container;
use Utopia\DI\Dependency;
use Utopia\DI\Exceptions\ContainerException;
use Utopia\DI\Exceptions\NotFoundException;

final class ContainerTest extends TestCase
{
    protected ?Container $container = null;

    public function setUp(): void
    {
        $this->container = new Container();

        $this->container
            ->set('age', fn (ContainerInterface $container): int => 25)
            ->set(
                'user',
                fn (ContainerInterface $container): string => 'John Doe is '.$container->get('age').' years old.'
            )
        ;
    }

    public function tearDown(): void
    {
        $this->container = null;
    }

    public function testImplementsPsrContainerInterface(): void
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
    }

    public function testResolution(): void
    {
        $this->assertSame('John Doe is 25 years old.', $this->container->get('user'));
    }

    public function testCanRegisterDependencyObjects(): void
    {
        $container = new Container();

        $container
            ->set(
                key: 'age',
                factory: new Dependency(
                    injections: [],
                    callback: fn (): int => 25
                )
            )
            ->set(
                key: 'john',
                factory: new Dependency(
                    injections: ['age'],
                    callback: fn (int $age): string => 'John Doe is '.$age.' years old.'
                )
            )
        ;

        $this->assertSame('John Doe is 25 years old.', $container->get('john'));
    }

    public function testFactoriesAreResolvedOncePerContainer(): void
    {
        $counter = 0;

        $this->container->set('counter', function (ContainerInterface $container) use (&$counter): int {
            $counter++;

            return $counter;
        });

        $this->assertSame(1, $this->container->get('counter'));
        $this->assertSame(1, $this->container->get('counter'));
    }

    public function testScopedContainersFallbackToParentDefinitions(): void
    {
        $request = $this->container->scope();

        $this->assertSame('John Doe is 25 years old.', $request->get('user'));
        $this->assertTrue($request->has('user'));
    }

    public function testScopedContainersCanOverrideParentDefinitions(): void
    {
        $request = $this->container->scope();

        $request
            ->set('age', fn (ContainerInterface $container): int => 30)
            ->set(
                'user',
                fn (ContainerInterface $container): string => 'John Doe is '.$container->get('age').' years old.'
            )
        ;

        $this->assertSame('John Doe is 30 years old.', $request->get('user'));
        $this->assertSame('John Doe is 25 years old.', $this->container->get('user'));
    }

    public function testScopeUsesParentCacheUntilDefinitionsAreOverridden(): void
    {
        $counter = 0;

        $this->container->set('counter', function (ContainerInterface $container) use (&$counter): int {
            $counter++;

            return $counter;
        });

        $request = $this->container->scope();

        $this->assertSame(1, $this->container->get('counter'));
        $this->assertSame(1, $request->get('counter'));

        $request->set('counter', function (ContainerInterface $container) use (&$counter): int {
            $counter++;

            return $counter;
        });

        $this->assertSame(2, $request->get('counter'));
        $this->assertSame(2, $request->get('counter'));
    }

    public function testCanCacheNullValues(): void
    {
        $counter = 0;

        $this->container->set('nullable', function (ContainerInterface $container) use (&$counter): null {
            $counter++;

            return null;
        });

        $this->assertNull($this->container->get('nullable'));
        $this->assertNull($this->container->get('nullable'));
        $this->assertSame(1, $counter);
    }

    public function testMissingDependencyThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Dependency not found: missing');

        $this->container->get('missing');
    }

    public function testFactoryFailuresThrowContainerException(): void
    {
        $this->container->set('broken', function (ContainerInterface $container): void {
            throw new RuntimeException('boom');
        });

        try {
            $this->container->get('broken');
            $this->fail('Expected a container exception.');
        } catch (ContainerException $exception) {
            $this->assertSame('Failed to resolve dependency "broken".', $exception->getMessage());
            $this->assertInstanceOf(RuntimeException::class, $exception->getPrevious());
            $this->assertSame('boom', $exception->getPrevious()->getMessage());
        }
    }

    public function testCircularDependenciesThrowContainerException(): void
    {
        $this->container
            ->set('a', fn (ContainerInterface $container) => $container->get('b'))
            ->set('b', fn (ContainerInterface $container) => $container->get('a'))
        ;

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Circular dependency detected for "a".');

        $this->container->get('a');
    }
}
