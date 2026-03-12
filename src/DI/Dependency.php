<?php

namespace Utopia\DI;

use Psr\Container\ContainerInterface;

class Dependency
{
    /**
     * @param  string[]  $injections
     * @param  callable  $callback
     */
    public function __construct(
        private readonly array $injections,
        private $callback,
    ) {
    }

    /**
     * Resolve the configured injections from the container and invoke the callback.
     */
    public function __invoke(ContainerInterface $container): mixed
    {
        $arguments = [];

        foreach ($this->injections as $injection) {
            $arguments[$injection] = $container->get($injection);
        }

        return ($this->callback)(...$arguments);
    }
}
