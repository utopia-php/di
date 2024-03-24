<?php

namespace Utopia\DI;

use Exception;

class DI
{
    /**
     * @var array
     */
    protected static array $callbacks = [];

    /**
     * @var array
     */
    protected array $resources = [];

    /**
     * Set a new resource callback
     *
     * @param  string  $name
     * @param  callable  $callback
     * @param  array  $injections
     * @return void
     *
     * @throws Exception
     */
    public static function setResource(string $name, callable $callback, array $injections = []): void
    {
        if ($name === 'utopia') {
            throw new Exception("'utopia' is a reserved keyword.", 500);
        }

        self::$callbacks[$name] = ['callback' => $callback, 'injections' => $injections, 'resets' => []];
    }

    /**
     * If a resource has been created return it, otherwise create it and then return it
     *
     * @param  string  $name
     * @param  bool  $fresh
     * @return mixed
     *
     * @throws Exception
     */
    public function getResource(string $name, bool $fresh = false): mixed
    {
        if ($name === 'utopia') {
            return $this;
        }

        if (!\array_key_exists($name, $this->resources) || $fresh || (self::$callbacks[$name]['reset'] ?? true)) {
            if (!\array_key_exists($name, self::$callbacks)) {
                throw new Exception('Failed to find resource: "' . $name . '"');
            }

            $this->resources[$name] = \call_user_func_array(
                self::$callbacks[$name]['callback'],
                $this->getResources(self::$callbacks[$name]['injections'])
            );
        }

        self::$callbacks[$name]['reset'] = false;

        return $this->resources[$name];
    }

    /**
     * Get Resources By List
     *
     * @param  array  $list
     * @return array
     */
    public function getResources(array $list): array
    {
        $resources = [];

        foreach ($list as $name) {
            $resources[$name] = $this->getResource($name);
        }

        return $resources;
    }

    /**
     * Get Resources By List
     *
     * @param  array  $list
     * @return array
     */
    public function inject(Hook $hook): mixed
    {
        $arguments = [];

        foreach ($hook->getParams() as $key => $param) {
            $value = $this->getResource($param['name']);
            $hook->setParamValue($key, $value);
            $arguments[$param['order']] = $value;
        }

        foreach ($hook->getInjections() as $key => $injection) {
            $arguments[$injection['order']] = $this->getResource($injection['name']);
        }

        \call_user_func_array($hook->getAction(), $arguments);
    }


}
