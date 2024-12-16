<?php

namespace Villeon\Library\Collection;

/**
 * MList.php
 * @package    Villeon\Library\Collection
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */
class MList extends MutableCollection
{

    /**
     * @return $this
     */
    public function flip(): static
    {
        $this->elements = array_flip($this->elements);
        return $this;
    }

    /**
     * @param ...$arrays
     * @return $this
     */
    public function merge(...$arrays): static
    {
        $this->elements = array_merge($this->elements, ...$arrays);
        return $this;
    }
}
