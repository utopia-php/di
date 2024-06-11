<?php

namespace Utopia\DI;

use Exception;

class Container
{
    /**
     * @var array
     */
    protected array $dependencies = [];

    /**
     * @var array
     */
    protected array $instances = [];

    public function __construct()
    {
        $di = new Dependency();
        $di->setName('di');
        $di->setCallback(function () {
            return $this;
        });
        $this->dependencies[$di->getName()] = $di;
    }

    /**
     * Set a dependency.
     *
     * @param  Dependency|Injection  $dependency
     * @return self
     *
     * @throws Exception
     */
    public function set(Dependency|Injection $dependency): self
    {
        if ($dependency->getName() === 'di') {
            throw new Exception("'di' is a reserved keyword.");
        }

        if (\array_key_exists($dependency->getName(), $this->instances)) {
            unset($this->instances[$dependency->getName()]);
        }

        $this->dependencies[$dependency->getName()] = $dependency;

        return $this;
    }

    /**
     * Get a dependency.
     *
     * @param  string  $name
     *
     * @return mixed
     */
    public function get(string $name): mixed
    {
        if (!\array_key_exists($name, $this->dependencies)) {
            throw new Exception('Failed to find dependency: "' . $name . '"');
        }

        return $this->inject($this->dependencies[$name]);
    }

    /**
     * Check if a dependency exists.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->dependencies);
    }

    /**
     * Resolve the dependencies of a given injection.
     *
     * @param  Injection  $injection
     * @param  bool  $fresh
     *
     * @return mixed
     */
    public function inject(Injection $injection, bool $fresh = false): mixed // Route
    {
        if (\array_key_exists($injection->getName(), $this->instances) && !$fresh) {
            return $this->instances[$injection->getName()];
        }

        $arguments = [];

        foreach ($injection->getDependencies() as $dependency) {

            if (\array_key_exists($dependency, $this->instances)) {
                $arguments[] = $this->instances[$dependency];
                continue;
            }

            if (!\array_key_exists($dependency, $this->dependencies)) {
                throw new Exception('Failed to find dependency: "' . $dependency . '"');
            }

            $arguments[] = $this->get($dependency);

        }

        $resolved = \call_user_func_array($injection->getCallback(), $arguments);

        $this->instances[$injection->getName()] = $resolved;

        return $resolved;
    }
    /**
     * Refresh a dependency
     *
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function refresh(string $name): self
    {
        if(\array_key_exists($name, $this->instances)) {
            unset($this->instances[$name]);
        }

        return $this;
    }
}
