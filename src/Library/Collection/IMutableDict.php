<?php

namespace Villeon\Library\Collection;

use TypeError;

class IMutableDict extends AbstractDict
{
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->is_immutable();
    }
    private function is_immutable()
    {
        throw new TypeError(printf("%s can't be modified",get_class($this)));
    }
}
