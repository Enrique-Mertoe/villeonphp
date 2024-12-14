<?php

namespace Villeon\Library;

/**
 *
 */
class Str implements ObjectLibrary, \ArrayAccess
{
    /**
     * @var string
     */
    private string $str;

    /**
     * @param string $str
     */
    public function __construct(string $str)
    {
        $this->str = $str;
    }

    /**
     * @param string $str
     * @return static
     */
    public static function from(string $str): static
    {
        return new static($str);
    }

    /**
     * @param ...$needles
     * @return bool
     */
    public function contains(...$needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($this->str, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ...$needles
     * @return bool
     */
    public function containsAll(...$needles): bool
    {
        foreach ($needles as $needle) {
            if (!str_contains($this->str, $needle)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return $this
     */
    public function trim(): static
    {
        $this->str = trim($this->str);
        return $this;
    }

    /**
     * @return $this
     */
    public function trimEnd(): static
    {
        $this->str = rtrim($this->str);
        return $this;
    }

    /**
     * @return $this
     */
    public function trimStart(): static
    {
        $this->str = ltrim($this->str);
        return $this;
    }

    /**
     * @param ...$needles
     * @return bool
     */
    public function startsWith(...$needles): bool
    {
        foreach ($needles as $needle) {
            if (str_starts_with($this->str, $needle))
                return true;
        }
        return false;
    }

    /**
     * @param string|string[] $string
     * @param string|string[] $replace
     * @return Str
     */
    public function replace(string|array $string, string|array $replace): Str
    {
        $this->str = str_replace($string, $replace, $this->str);
        return $this;
    }

    /**
     * @param $delimiter
     * @param $limit
     * @param $useRegex
     * @return array
     */
    public function split($delimiter, $limit = null, $useRegex = false): array
    {
        if ($useRegex) {
            return preg_split($delimiter, $this->str, $limit ?: -1) ?: [];
        }

        return explode($delimiter, $this->str, $limit ?: PHP_INT_MAX);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->str;
    }

    /**
     * @return string[]|null
     */
    public function __debugInfo(): ?array
    {
        return [get_class($this) => $this->str];
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->str);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $offset >= 0 && $offset < strlen($this->str);
    }

    /**
     * @param mixed $offset
     * @return string|null
     */
    public function offsetGet(mixed $offset): ?string
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }
        return $this->str[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException("String is immutable; cannot modify characters directly.");
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException("String is immutable; cannot unset characters.");
    }
}
