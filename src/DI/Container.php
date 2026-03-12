<?php

namespace Utopia\DI;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Utopia\DI\Exceptions\ContainerException;
use Utopia\DI\Exceptions\NotFoundException;

/**
 * @phpstan-consistent-constructor
 */
class Container implements ContainerInterface
{
    /**
     * @var array<string, callable(ContainerInterface): mixed>
     */
    private array $definitions = [];

    /**
     * @var array<string, mixed>
     */
    private array $resolved = [];

    /**
     * @var array<string, true>
     */
    private array $resolving = [];

    public function __construct(
        private ?ContainerInterface $parent = null,
    ) {
    }

    /**
     * Register a dependency factory on the current container.
     *
     * @param  string  $key
     * @param  callable(ContainerInterface): mixed  $factory
     * @return static
     */
    public function set(string $key, callable $factory): static
    {
        $this->definitions[$key] = $factory;
        unset($this->resolved[$key]);

        return $this;
    }

    /**
     * Resolve an entry from the current container or its parent chain.
     *
     * @param  string  $id
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     */
    public function get(string $id): mixed
    {
        if (\array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        if (\array_key_exists($id, $this->definitions)) {
            if (isset($this->resolving[$id])) {
                throw new ContainerException('Circular dependency detected for "'.$id.'".');
            }

            $this->resolving[$id] = true;

            try {
                $resolved = ($this->definitions[$id])($this);
            } catch (NotFoundException $exception) {
                throw $exception;
            } catch (ContainerExceptionInterface $exception) {
                throw $exception;
            } catch (\Throwable $exception) {
                throw new ContainerException(
                    'Failed to resolve dependency "'.$id.'".',
                    previous: $exception
                );
            } finally {
                unset($this->resolving[$id]);
            }

            $this->resolved[$id] = $resolved;

            return $resolved;
        }

        if ($this->parent instanceof ContainerInterface) {
            return $this->parent->get($id);
        }

        throw new NotFoundException('Dependency not found: '.$id);
    }

    public function has(string $id): bool
    {
        if (\array_key_exists($id, $this->definitions)) {
            return true;
        }

        return $this->parent?->has($id) ?? false;
    }

    /**
     * Create a child container that falls back to the current container.
     *
     * @return static
     */
    public function scope(): static
    {
        return new static($this);
    }
}
