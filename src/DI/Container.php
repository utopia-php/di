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
    protected array $resources = [];

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
    public function get(Injection $injection): mixed
    {
        $arguments = [];

        foreach ($injection->getDependencies() as $dependency) {
            if ($dependency->getName() === 'utopia') {
                return $this;
            }
    
            if (!\array_key_exists($dependency->getName(), $this->resources)) {
                if (!\array_key_exists($dependency->getName(), $this->dependencies)) {
                    throw new Exception('Failed to find resource: "' . $dependency->getName() . '"');
                }
    
                $this->resources[$dependency->getName()] = \call_user_func_array(
                    $this->dependencies[$dependency->getName()]->getCallback(),
                    $this->get($this->dependencies[$dependency->getName()]->getDependencies())
                );
            }
    
            $arguments[] = $this->get($dependency->getName());
        }

        \call_user_func_array($injection->getCallback(), $arguments);
    }
}
