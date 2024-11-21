<?php

namespace Villeon\core\Collection;

use ArrayAccess;

class Collection implements ArrayAccess
{
    public int $size;
    private array $container = [];

    public static function from_array(array|null $options): Collection
    {
        return new Collection($options);
    }

    public function __construct($items)
    {
        $this->container = $items ?? [];
        $this->size = count($this->container);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);

    }

    public function each($callback): static
    {
        if (is_callable($callback))
            foreach ($this->container as $item) {
                $res = call_user_func($callback, $item);
                if (!(gettype($res) === "NULL")) {
                    return $res;
                }
            }
        return $this;
    }

    function has($item): bool
    {
        return (bool)array_search($item, $this->container);
    }

    function array(): array
    {
        return $this->container;
    }
}
