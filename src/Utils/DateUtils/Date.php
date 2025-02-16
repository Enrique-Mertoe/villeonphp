<?php

namespace Villeon\Utils\DateUtils;

use DateTime;
use DateTimeZone;
use Throwable;

class Date
{
    private DateTime $date;
    public int $time;


    /**
     * @throws Throwable
     */
    public function __construct(string $time = "now", ?DateTimeZone $timezone = null)
    {
        $this->date = new DateTime($time, $timezone);
        $this->time = $this->date->getTimestamp();
    }

    public static function now(): self
    {
        return new self();
    }

    /**
     * @throws Throwable
     */
    public static function createFromFormat(string $format, string $time, ?DateTimeZone $timezone = null): ?self
    {
        $date = DateTime::createFromFormat($format, $time, $timezone);
        return $date ? new self($date->format('Y-m-d H:i:s'), $timezone) : null;
    }

    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    /**
     * @throws Throwable
     */
    public function addDays(int $days): self
    {
        $this->date->modify("+{$days} days");
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function subDays(int $days): self
    {
        $this->date->modify("-{$days} days");
        return $this;
    }

    public function diffInDays(Date $other): int
    {
        return (int)$this->date->diff($other->date)->days;
    }

    public function isPast(): bool
    {
        return $this->date < new DateTime();
    }

    public function isFuture(): bool
    {
        return $this->date > new DateTime();
    }

}
