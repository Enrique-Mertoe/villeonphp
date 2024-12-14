<?php

namespace Villeon\Library;

class Pair
{
    public string|int $key;
    public mixed $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public static function from(array $array): static
    {
        return new static($array[0] ?? null, $array[1] ?? null);
    }
}
