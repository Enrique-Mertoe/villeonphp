<?php

namespace Villeon\Library\Collection;

interface CollectionAccess
{
    public function get($key, $default = null): mixed;

    public function set($offset, $value): void;

    public function has($keys): bool;

    public function addAll(array|Collection $collection): static;
}
