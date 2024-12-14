<?php

namespace Villeon\Library\Collection;

use Traversable;
use Villeon\Library\ObjectLibrary;

/**
 * Collection Class
 * @template T
 * This class represents a collection of elements and implements the CollectionIterable
 * interface. The class provides methods to retrieve elements, find indices, check
 * for emptiness, slice the collection, and get the first and last elements.
 *
 * @package    CustomCollection
 * @author     Your Name <youremail@example.com>
 * @version    1.0.0
 */
abstract class Collection implements CollectionIterable,
    \JsonSerializable, ObjectLibrary
{
    /**
     * @var array<T>
     */

    protected array $elements;
    /**
     * @var int
     */
    public int $size;

    /**
     * @param Collection<T>|array<T> $collection
     * @return static<T>
     */
    public static function from(Collection|array $collection): static
    {
        return new static($collection);
    }

    /**
     * @param array<T> $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
        $this->size = count($this->elements);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {

        return $this->elements;
    }

    /**
     * @return mixed
     */
    public function first(): mixed
    {
        return reset($this->elements) ?: null;
    }

    /**
     * @return mixed
     */
    public function last(): mixed
    {
        return end($this->elements) ?: null;
    }

    /**
     * @param $index
     * @param $default
     * @return T
     */

    public function get($index, $default = null): mixed
    {
        return $this->elements[$index] ?? null;
    }

    /**
     * @param mixed $element
     * @return int
     */
    public function indexOf(mixed $element): int
    {
        $index = array_search($element, $this->elements, true);
        return $index === false ? -1 : $index;
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function each(callable $callback): void
    {
        foreach ($this->elements as $element) {
            $callback($element);
        }
    }

    /**
     * @param int $start
     * @param int $length
     * @return mixed
     */
    public function slice(int $start, int $length): mixed
    {
        $sliced = array_slice($this->elements, $start, $length);
        return new static($sliced);
    }

    /**
     * @return $this
     */
    public function sorted(): static
    {
        $elements = $this->elements;
        sort($elements);
        return new static($elements);
    }

    /**
     * @param mixed $element
     * @return bool
     */
    public function has(...$elements): bool
    {
        foreach ($elements as $element)
            if (in_array($element,$this->elements))
                return true;
        return false;
    }

    public function hasAll(...$elements): bool
    {
        foreach ($elements as $element)
            if (!is_resource($this->elements[$element]))
                return false;
        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function filter(callable $callback): static
    {
        $filteredItems = [];

        foreach ($this->elements as $item) {
            if ($callback($item)) {
                $filteredItems[] = $item;
            }
        }

        return new static($filteredItems);
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    public function map(callable $callback): mixed
    {
        $mappedItems = [];

        foreach ($this->elements as $item) {
            $mappedItems[] = $callback($item);
        }

        return $mappedItems;
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    public function find(callable $callback): mixed
    {
        foreach ($this->elements as $item) {
            if ($callback($item)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return '[' . implode(', ', $this->elements) . ']';
    }

    /**
     * @return string[]|null
     */
    public function __debugInfo(): ?array
    {
//        return [get_class($this) => '[' . implode(', ', $this->elements) . ']'];
        return $this->elements;
    }

    public function empty(): bool
    {
        return empty($this->elements);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }
}
