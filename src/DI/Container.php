<?php

namespace Utopia\DI;

use Exception;

class Container
{
    public const DEFAULT_CONTEXT = 'utopia';

    /**
     * @var array<string, array<string, Resource>>
     */
    protected array $dependencies = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $instances = [];

    /**
     * Set a dependency.
     *
     * @param  Resource  $dependency
     * @param  string  $context
     * @return self
     *
     * @throws Exception
     */
    public function set(Resource $dependency, string $context = self::DEFAULT_CONTEXT): self
    {
        if ($dependency->getName() === 'di') {
            throw new Exception("'di' is a reserved keyword.");
        }

        $this->dependencies[$context] ??= [];
        $this->instances[$context] ??= [];

        unset($this->instances[$context][$dependency->getName()]);
        $this->dependencies[$context][$dependency->getName()] = $dependency;

        return $this;
    }

    /**
     * Register a callable resource.
     *
     * @param  string  $name
     * @param  callable  $callback
     * @param  string[]  $dependencies
     * @param  string  $context
     * @return self
     */
    public function setResource(string $name, callable $callback, array $dependencies = [], string $context = self::DEFAULT_CONTEXT): self
    {
        $resource = new Resource();
        $resource
            ->setName($name)
            ->setCallback($callback)
        ;

        foreach ($dependencies as $dependency) {
            $resource->inject($dependency);
        }

        return $this->set($resource, $context);
    }

    /**
     * Get a resource.
     *
     * @param  string  $name
     * @param  string  $context
     * @param  bool  $fresh
     * @return mixed
     *
     * @throws Exception
     */
    public function get(string $name, string $context = self::DEFAULT_CONTEXT, bool $fresh = false): mixed
    {
        if ($name === 'di') {
            return $this;
        }

        $injection = $this->getDefinition($name, $context);

        return $this->inject($injection, $context, $fresh);
    }

    /**
     * Alias for get().
     *
     * @param  string  $name
     * @param  string  $context
     * @param  bool  $fresh
     * @return mixed
     *
     * @throws Exception
     */
    public function getResource(string $name, string $context = self::DEFAULT_CONTEXT, bool $fresh = false): mixed
    {
        return $this->get($name, $context, $fresh);
    }

    /**
     * Resolve multiple dependencies for a context.
     *
     * @param  string[]  $names
     * @param  string  $context
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getResources(array $names, string $context = self::DEFAULT_CONTEXT): array
    {
        $resources = [];

        foreach ($names as $name) {
            $resources[$name] = $this->get($name, $context);
        }

        return $resources;
    }

    /**
     * Check if a resource exists in the current or default context.
     *
     * @param  string  $name
     * @param  string  $context
     * @return bool
     */
    public function has(string $name, string $context = self::DEFAULT_CONTEXT): bool
    {
        if ($name === 'di') {
            return true;
        }

        return isset($this->dependencies[$context][$name]) || isset($this->dependencies[self::DEFAULT_CONTEXT][$name]);
    }

    /**
     * Resolve the dependencies of a given resource.
     *
     * @param  Resource  $injection
     * @param  string  $context
     * @param  bool  $fresh
     * @return mixed
     *
     * @throws Exception
     */
    public function inject(Resource $injection, string $context = self::DEFAULT_CONTEXT, bool $fresh = false): mixed
    {
        $this->instances[$context] ??= [];

        if (\array_key_exists($injection->getName(), $this->instances[$context]) && !$fresh) {
            return $this->instances[$context][$injection->getName()];
        }

        $arguments = [];

        foreach ($injection->getDependencies() as $dependency) {
            $arguments[] = $this->get($dependency, $context);
        }

        $resolved = \call_user_func_array($injection->getCallback(), $arguments);
        $this->instances[$context][$injection->getName()] = $resolved;

        return $resolved;
    }

    /**
     * Refresh a dependency instance.
     *
     * @param  string  $name
     * @param  string|null  $context
     * @return self
     */
    public function refresh(string $name, ?string $context = null): self
    {
        if ($name === 'di') {
            return $this;
        }

        if ($context !== null) {
            unset($this->instances[$context][$name]);

            return $this;
        }

        foreach (\array_keys($this->instances) as $instanceContext) {
            unset($this->instances[$instanceContext][$name]);
        }

        return $this;
    }

    /**
     * Remove context-specific registrations and cached instances.
     *
     * @param  string  $context
     * @return self
     */
    public function purge(string $context): self
    {
        if ($context === self::DEFAULT_CONTEXT) {
            $this->instances[$context] = [];

            return $this;
        }

        unset($this->dependencies[$context], $this->instances[$context]);

        return $this;
    }

    /**
     * @param  string  $name
     * @param  string  $context
     * @return Resource
     *
     * @throws Exception
     */
    protected function getDefinition(string $name, string $context): Resource
    {
        if (isset($this->dependencies[$context][$name])) {
            return $this->dependencies[$context][$name];
        }

        if (isset($this->dependencies[self::DEFAULT_CONTEXT][$name])) {
            return $this->dependencies[self::DEFAULT_CONTEXT][$name];
        }

        throw new Exception('Failed to find dependency: "' . $name . '"');
    }
}
