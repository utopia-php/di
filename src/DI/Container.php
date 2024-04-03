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
        if ($dependency->getName() === 'utopia') {
            throw new Exception("'utopia' is a reserved keyword.", 500);
        }

        $this->dependencies[$dependency->getName()] = $dependency;

        return $this;
    }

    /**
     * TBD
     *
     * @param  array  $list
     * @return array
     */
    public function get(Injection $injection): mixed // Route
    {
        if (\array_key_exists($injection->getName(), $this->instances)) {
            return $this->instances[$injection->getName()];
        }

        $arguments = [];

        foreach ($injection->getDependencies() as $dependency) {

            if (\array_key_exists($dependency->getName(), $this->instances)) {
                $arguments[] = $this->instances[$dependency->getName()];
                continue;
            }
            
            if (!\array_key_exists($dependency->getName(), $this->dependencies)) {
                throw new Exception('Failed to find dependency: "' . $dependency->getName() . '"');
            }

            $arguments[] = $this->get($dependency->getName());
    
        }

        $resolved = \call_user_func_array($injection->getCallback(), $arguments);

        $this->instances[$injection->getName()] = $resolved;

        return $resolved;
    }
}
