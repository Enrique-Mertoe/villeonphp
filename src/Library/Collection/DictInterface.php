<?php

namespace Villeon\Library\Collection;

/**
 *
 */
interface DictInterface
{
    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null): mixed;

    /**
     * @return mixed
     */
    public function keys(): array;

    /**
     * @return mixed
     */
    public function items(): array;
}
