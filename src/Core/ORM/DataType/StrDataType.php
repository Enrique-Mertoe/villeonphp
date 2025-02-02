<?php

namespace Villeon\Core\ORM\DataType;


class StrDataType extends DataType
{
    private ?int $length;

    /**
     * @param int|null $length
     */
    public function __construct(?int $length = 255)
    {
        parent::__construct('STRING', 'varchar');
        $this->length = $length;
    }

    /**
     * @param mixed|null $default
     * @return string
     */
    public function toSql(mixed $default = null): string
    {
        $default = str($default ?? "")->trim();
        print_r($default->empty());
        return "VARCHAR(" . $this->length . ")"
            . (!$default->empty() ? " DEFAULT '$default'" : "");
    }
}
