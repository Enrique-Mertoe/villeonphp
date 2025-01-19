<?php

namespace Villeon\Library\Collection;

use Exception;

/**
 *
 */
class ChainMap extends MutableMap
{
    /**
     * @var array $maps
     */
    private array $maps;

    /**
     * @param ...$maps
     */
    public function __construct(...$maps)
    {
        $this->container = !empty($maps) ? $maps : [array()];
        $this->maps = &$this->container;
    }

    /**
     * @throws KeyError
     */
    public function offsetGet($offset)
    {
        foreach ($this->maps as $mapping) {
            if (array_key_exists($offset, $mapping))
                return $mapping[$offset];
        }
        return $this->__missing($offset);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        foreach ($this->maps as $mapping) {
            if (array_key_exists($offset, $mapping))
                return true;
        }
        return false;
    }

    /**
     * @param $offset
     * @param $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->maps[0][$offset] = $value;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed|void|null
     */
    public function get($key, $default = null)
    {
        return $this[$key] ?? $default;
    }

    /**
     * @throws KeyError
     */
    public function pop($key): mixed
    {
        if (isset($this[$key])) {
            $v = $this[$key];
            unset($this[$key]);
            return $v;
        }
        throw new KeyError("Key not found in the first mapping $key");
    }

    /**
     * @return $this
     */
    public function parents(): static
    {
        return new static(...array_slice($this->maps, 1));
    }

    /**
     * @param array<string|int,mixed> $child
     * @return $this
     */
    public function newChild(array $child): static
    {
        return new static($child, ...$this->maps);
    }

}
