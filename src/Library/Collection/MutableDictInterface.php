<?php

namespace Villeon\Library\Collection;

interface MutableDictInterface
{
    public function update(array $items,...$kw_args);
    public function pop(string $offset);
    public function clear();
}
