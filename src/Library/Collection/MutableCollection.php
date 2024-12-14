<?php

namespace Villeon\Library\Collection;

class MutableCollection extends Collection implements MutableCollectionIterable,
    \ArrayAccess
{

    public function reverse(): mixed
    {
        $reversed = array_reverse($this->elements);
        return new self($reversed);
    }

    public function sort(callable $comparator = null): void
    {
        if ($comparator) {
            usort($this->elements, $comparator);
        } else {
            sort($this->elements);
        }
    }

    public function add(mixed $element): void
    {
        $this->elements[] = $element;
    }

    public function remove(mixed $element): bool
    {
        $index = array_search($element, $this->elements, true);
        if ($index !== false) {
            unset($this->elements[$index]);
            $this->elements = array_values($this->elements);
            return true;
        }
        return false;
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function set(int $index, mixed $element): bool
    {
        if (isset($this->elements[$index])) {
            $this->elements[$index] = $element;
            return true;
        }
        return false;
    }

    public function insert(int $index, mixed $element): void
    {
        array_splice($this->elements, $index, 0, [$element]);
    }

    public function replace(callable $callback): void
    {
        foreach ($this->elements as $key => $element) {
            $this->elements[$key] = $callback($element);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return static::get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        static::set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        static::remove($offset);
    }
}
