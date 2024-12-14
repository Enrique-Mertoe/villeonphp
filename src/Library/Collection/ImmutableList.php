<?php

namespace Villeon\Library\Collection;

class ImmutableList extends Collection
{
    public function map(callable $callback): static
    {
        $mappedItems = [];

        foreach ($this->elements as $item) {
            $mappedItems[] = $callback($item);
        }

        return new static($mappedItems);
    }
}
