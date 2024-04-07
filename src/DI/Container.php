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
     * TBD
     *
     * @param  Dependency  $dependency
     * @return self
     *
     * @throws Exception
     */
    public function set(Dependency $dependency): self
    {
        if ($dependency->getName() === 'di') {
            throw new Exception("'di' is a reserved keyword.", 500);
        }

        $this->dependencies[$dependency->getName()] = $dependency;

        return $this;
    }

    public function get(string $name): mixed
    {
        if (!\array_key_exists($name, $this->dependencies)) {
            throw new Exception('Failed to find dependency: "' . $name . '"');
        }

        return $this->inject($this->dependencies[$name]);
    }

    /**
     * Resolve the dependencies of a given injection.
     *
     * @param  Injection  $injection
     * @return mixed
     */
    public function inject(Injection $injection): mixed // Route
    {
        if (\array_key_exists($injection->getName(), $this->instances)) {
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
}
