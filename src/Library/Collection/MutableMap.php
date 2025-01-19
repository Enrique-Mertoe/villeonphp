<?php

namespace Villeon\Library\Collection;

use ReturnTypeWillChange;

class MutableMap implements \ArrayAccess, \JsonSerializable
{
    protected $container;

    #[ReturnTypeWillChange] public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
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

    protected function pop($key): mixed
    {

    }

    public function __debugInfo(): ?array
    {
        return [json_encode($this->container)];
    }
}
