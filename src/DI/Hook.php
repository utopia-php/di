<?php

namespace Utopia\DI;

class Hook
{
    protected array $groups = [];
    protected array $params = [];
    protected array $injections = [];
    protected int $order = 0;
    protected $action = null;

    /**
     * Set hook action.
     *
     * @param callable $action
     * @return static
     */
    public function action(callable $action): static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get hook action.
     *
     * @return callable
     */
    public function getAction(): callable
    {
        if ($this->action === null) {
            return static function () {
            };
        }

        return $this->action;
    }

    /**
     * Set hook groups.
     *
     * @param array $groups
     * @return static
     */
    public function groups(array $groups): static
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Get hook groups.
     *
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Register an injection.
     *
     * @param string $name
     * @param int|null $order
     * @return static
     */
    public function inject(string $name, ?int $order = null): static
    {
        $order = $this->resolveOrder($order);
        $this->injections[] = ['name' => $name, 'order' => $order];
        return $this;
    }

    /**
     * Get injections.
     *
     * @return array
     */
    public function getInjections(): array
    {
        return $this->injections;
    }

    /**
     * Register a param.
     *
     * @param string $name
     * @param mixed $validator
     * @param mixed $default
     * @param bool $optional
     * @param array $injections
     * @param int|null $order
     * @return static
     */
    public function param(
        string $name,
        mixed $validator = null,
        mixed $default = null,
        bool $optional = false,
        array $injections = [],
        ?int $order = null
    ): static {
        $order = $this->resolveOrder($order);
        $this->params[$name] = [
            'validator' => $validator,
            'default' => $default,
            'optional' => $optional,
            'injections' => $injections,
            'order' => $order,
            'value' => $default,
        ];

        return $this;
    }

    /**
     * Get params.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Store a param value.
     *
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function setParamValue(string $name, mixed $value): static
    {
        if (isset($this->params[$name])) {
            $this->params[$name]['value'] = $value;
        }

        return $this;
    }

    protected function resolveOrder(?int $order): int
    {
        if ($order === null) {
            return $this->order++;
        }

        $this->order = max($this->order, $order + 1);
        return $order;
    }
}
