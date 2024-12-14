<?php

namespace Villeon\Library\Collection;

class Dict extends AbstractDict implements MutableDictInterface
{

    public function pop($offset)
    {
        $e = $this->elements[$offset];
        unset($this->elements[$offset]);
        $this->modified();
        return $e;
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function update(array $items, ...$kw_args)
    {
        // TODO: Implement update() method.
    }
}
