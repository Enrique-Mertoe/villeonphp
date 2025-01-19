<?php

namespace Villeon\Library\Collection;

class KeyError extends LookupError
{
    public function __construct($key = "")
    {
        parent::__construct("Key '$key' not found in any map.");
    }
}
