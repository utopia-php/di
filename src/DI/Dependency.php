<?php

namespace Utopia\DI;

class Dependency extends Injection
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
}