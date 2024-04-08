<?php

namespace Utopia\DI;

use Exception;

abstract class Injection
{
    protected array $dependencies = [];
    protected $callback;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set Callback
     *
     * @param  mixed  $callback
     * @return self
     */
    public function setCallback(mixed $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

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
     * Get the value of dependencies
     *
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Depenedency
     *
     * @param  string  $name
     * @return self
     *
     * @throws Exception
     */
    public function inject(string $name): self
    {
        if (array_key_exists($name, $this->dependencies)) {
            throw new Exception('Dependency already declared for '.$name);
        }

        $this->dependencies[] = $name;

        return $this;
    }
}
