<?php

namespace Villeon\Library\Collection;

/**
 * MutableCollectionIterable Interface
 *
 * This interface extends the CollectionIterable interface and adds methods
 * for modifying the collection. Methods include adding, removing, sorting, and
 * modifying elements in the collection.
 *
 * @package    CustomCollection
 * @author     Your Name <youremail@example.com>
 * @version    1.0.0
 */
interface MutableCollectionIterable
{
    /**
     * Reverses the order of the elements in the collection.
     * This method returns a new collection with the order of elements reversed.
     *
     * @return mixed A new collection with elements in reversed order.
     */
    public function reverse(): mixed;

    /**
     * Sorts the elements of the collection.
     * This method sorts the elements based on a provided comparator or natural order.
     *
     * @param callable|null $comparator An optional comparator function to define custom sorting.
     * @return void A new collection with the sorted elements.
     */
    public function sort(callable $comparator = null): void;

    /**
     * Adds an element to the collection.
     * This method modifies the collection by appending an element.
     *
     * @param mixed $element The element to add to the collection.
     */
    public function add(mixed $element);

    /**
     * Removes an element from the collection.
     * This method removes the first occurrence of the element in the collection.
     *
     * @param mixed $element The element to remove.
     * @return bool True if the element was found and removed, false otherwise.
     */
    public function remove(mixed $element): bool;

    /**
     * Clears all elements from the collection.
     * This method removes every element from the collection.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Sets an element at a specific index in the collection.
     * This method modifies the element at the given index.
     *
     * @param int $index The index to set the element at.
     * @param mixed $element The element to set at the specified index.
     * @return bool True if the element was successfully set, false otherwise.
     */
    public function set(int $index, mixed $element): bool;

    /**
     * Inserts an element at a specific index in the collection.
     * This method shifts elements if necessary to accommodate the new element.
     *
     * @param int $index The index where the element should be inserted.
     * @param mixed $element The element to insert.
     * @return void
     */
    public function insert(int $index, mixed $element): void;

    /**
     * Replaces all elements in the collection that match a condition.
     * This method replaces elements that meet the criteria defined in the callback with new values.
     *
     * @param callable $callback A function to check each element.
     * @return void
     */
    public function replace(callable $callback): void;
}
