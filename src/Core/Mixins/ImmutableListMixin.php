<?php

namespace Villeon\Core\Mixins;

use TypeError;

trait ImmutableListMixin
{

    public function pop(): void
    {
        $this->is_immutable();
    }

    public function add($key): void
    {
        $this->is_immutable();
    }

    private function is_immutable()
    {
        throw new TypeError(sprintf("'%s' objects are immutable", get_class($this)));

    }
}
