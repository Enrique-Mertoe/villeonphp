<?php

namespace Villeon\Library\Collection;

use ArrayAccess;
use JsonSerializable;
use ReturnTypeWillChange;

abstract class MutableMap implements ArrayAccess, JsonSerializable
{
    protected $container;

    #[ReturnTypeWillChange] public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    #[ReturnTypeWillChange] public function offsetGet($offset)
    {
    }

    #[ReturnTypeWillChange] public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    #[ReturnTypeWillChange] public function offsetUnset($offset): void
    {
        unset($this->container[0][$offset]);
    }

    /**
     * @throws KeyError
     */
    protected function __missing($key)
    {
        throw new KeyError($key);
    }

    public function jsonSerialize(): mixed
    {
        return $this->container;
    }

    #[ReturnTypeWillChange]
    protected function pop($key): mixed
    {
    }

    public function __debugInfo(): ?array
    {
        return [json_encode($this->container)];
    }
}
