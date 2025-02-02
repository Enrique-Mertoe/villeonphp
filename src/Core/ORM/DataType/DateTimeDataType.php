<?php

namespace Villeon\Core\ORM\DataType;

class DateTimeDataType extends DataType
{
    /**
     * @param string|null $default Default value for the DATETIME column.
     */
    public function __construct(?string $default = null)
    {
        parent::__construct('DATETIME', 'datetime');
    }

    /**
     * Converts the DATETIME definition to SQL syntax.
     *
     * @param mixed $default
     * @return string The SQL definition for a DATETIME column.
     */
    public function toSql(mixed $default = null): string
    {
        $sql = "DATETIME";

        if ($default !== null) {
            if (strtoupper($default) === 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $sql .= " DEFAULT '$default'";
            }
        }

        return $sql;
    }
}
