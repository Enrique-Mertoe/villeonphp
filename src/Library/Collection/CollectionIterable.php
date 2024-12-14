<?php

namespace Villeon\Library\Collection;
/**
 * CollectionOperator Interface
 * @template T
 *
 * This interface defines a set of collection manipulation methods that resemble common operations
 * performed on collections in programming. The interface outlines methods for getting, sorting,
 * and manipulating elements in a collection, similar to common Kotlin collection operations.
 * Implementing this interface allows for the creation of custom collections with these features.
 *
 * @package    CustomCollection
 * @author     Your Name <youremail@example.com>
 * @version    1.0.0
 */
interface CollectionIterable extends \IteratorAggregate,\Countable
{
    /**
     * Retrieves the first element in the collection.
     * Returns the first element of the collection if it exists.
     *
     * @return T|mixed The first element of the collection.
     */
    public function first(): mixed;

    /**
     * Retrieves the last element in the collection.
     * Returns the last element of the collection if it exists.
     *
     * @return T|mixed The last element of the collection.
     */
    public function last(): mixed;

    /**
     * Retrieves the element at a specific index in the collection.
     * This method allows accessing elements by index.
     *
     * @return T The element at the specified index.
     */
    public function get($index, $default = null): mixed;

    /**
     * Finds the index of a given element in the collection.
     * This method returns the first index at which the element is found.
     *
     * @param T|mixed $element The element to search for.
     * @return int The index of the element, or -1 if not found.
     */
    public function indexOf(mixed $element): int;

    /**
     * Iterates through each element in the collection.
     * Typically used for side effects, such as applying a function to each element.
     *
     * @param callable $callback A function to apply to each element.
     * @return void
     */
    public function each(callable $callback): void;


    /**
     * Retrieves a subset of the collection based on given parameters (e.g., range).
     * Typically used to slice a collection into a new subset of elements.
     *
     * @param int $start The starting index for the slice.
     * @param int $length The number of elements to include in the slice.
     * @return mixed A new collection containing the sliced elements.
     */
    public function slice(int $start, int $length): mixed;

    /**
     * Sorts the collection and returns a new sorted collection.
     * Does not modify the original collection, adhering to immutability principles.
     *
     * @return mixed A new collection with sorted elements.
     */
    public function sorted(): static;

    /**
     * Checks if the collection contains a specific element.
     *
     * @param mixed $element The element to search for.
     * @return bool True if the collection contains the element, false otherwise.
     */
    public function has(mixed $element): bool;

    /**
     * Converts the collection to a native array.
     *
     * @return array The array representation of the collection.
     */
    public function toArray(): array;

    /**
     * Returns a new collection containing elements that match a given condition.
     *
     * @param callable $callback A function to test each element.
     * @return mixed A new collection with the filtered elements.
     */
    public function filter(callable $callback): static;

    /**
     * Returns a new collection where each element is transformed by a given function.
     *
     * @param callable $callback A function to apply to each element.
     * @return mixed A new collection with the transformed elements.
     */
    public function map(callable $callback): mixed;

    /**
     * Finds and returns the first element that matches a given condition.
     *
     * @param callable $callback A function to test each element.
     * @return mixed The first element that matches the condition, or null if not found.
     */
    public function find(callable $callback): mixed;
}
