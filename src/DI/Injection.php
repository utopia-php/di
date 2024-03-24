<?php

namespace Utopia\DI;

use Exception;

class Injection
{
    protected array $dependencies = [];
    protected $callback;

    /**
     * Get the value of callback
     *
     * @return mixed
     */
    public function getCallback(): mixed
    {
        return $this->callback;
    }

    /**
     * Set Callback
     *
     * @param  mixed  $callback
     * @return self
     */
    public function callback(mixed $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Get the value of dependencies
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Depenedency
     *
     * @param  string  $dependency
     * @return self
     *
     * @throws Exception
     */
    public function dependency(string $name): self
    {
        if (array_key_exists($name, $this->dependencies)) {
            throw new Exception('Dependency already declared for '.$name);
        }

        $this->dependencies[] = $name;

        return $this;
    }
}