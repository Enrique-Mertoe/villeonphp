<?php

namespace Villeon\Library\Collection;

class Dict extends AbstractDict implements MutableDictInterface
{

    public function pop($offset)
    {
        $e = $this->elements[$offset] ?? null;
        unset($this->elements[$offset]);
        $this->modified();
        return $e;
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function update(array $items, ...$kw_args): static
    {
        $this->elements = array_merge($this->elements, $items);
        return $this;
    }

    public function hasKey(...$keys): bool
    {
        foreach ($keys as $key)
            if (array_key_exists($key, $this->elements))
                return true;
        return true;
    }

    public function set(int $position, string $key, mixed $value): bool
    {

    }

    public function keySort(int $flags = SORT_REGULAR): static
    {
        ksort($this->elements, $flags);
        return $this;
    }

    public function valueSort(int $flags = SORT_REGULAR): static
    {
        asort($this->elements, $flags);
        return $this;
    }

    public function each(callable $callback): void
    {
        try {
            $ref = new \ReflectionFunction($callback);
            $params = count($ref->getParameters());
            if ($params == 1) {
                foreach ($this->elements as $element) {
                    $callback($element);
                }
            } else {
                foreach ($this->elements as $key => $value) {
                    $callback($key, $value);
                }
            }
        } catch (\Throwable $e) {
            log_error($e);
        }
    }
}
