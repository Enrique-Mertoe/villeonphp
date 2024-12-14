<?php

namespace Villeon\Library\Collection;

use Traversable;
use Villeon\Library\Pair;

class AbstractDict extends Collection implements
    DictInterface, \IteratorAggregate, \Countable
    , \ArrayAccess
{
    /**
     * @var array<string|int,mixed> $elements ;
     */
    protected array $elements;

    /**
     * @param array<string|int,mixed> $elements ;
     */
    public function __construct(array $elements)
    {
        parent::__construct($elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->elements);
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
        $this->elements[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->elements[$offset]);
    }

    public function get($key, $default = null): mixed
    {
        return $this->elements[$key] ?? $default;
    }

    public function keys(): array
    {
        return array_keys($this->elements);
    }

    public function items(): array
    {
        return array_values($this->elements);
    }

    public function first(): Pair
    {
        $key = array_key_first($this->elements);
        return Pair::from([$key, $this->elements[$key]]);
    }

    public function last(): Pair
    {
        $key = array_key_last($this->elements);
        return Pair::from([$key, $this->elements[$key]]);
    }
    public function __debugInfo(): ?array
    {
        return [$this->elements];
    }
    protected function modified(): void
    {
        $this->size = count($this->elements);
    }
}
