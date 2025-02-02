<?php

namespace Villeon\Core\ORM\DataType;

class TimestampDataType extends DataType
{
    private bool $onUpdateCurrent;

    /**
     * @param bool $onUpdateCurrent Whether to set ON UPDATE CURRENT_TIMESTAMP.
     */
    public function __construct(bool $onUpdateCurrent = false)
    {
        parent::__construct('TIMESTAMP', 'timestamp');
        $this->onUpdateCurrent = $onUpdateCurrent;
    }

    /**
     * Converts the TIMESTAMP definition to SQL syntax.
     *
     * @param mixed|null $default
     * @return string The SQL definition for a TIMESTAMP column.
     */
    public function toSql(mixed $default = null): string
    {
        $sql = "TIMESTAMP";

        if ($default !== null) {
            if (strtoupper($default) === 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $sql .= " DEFAULT '$default'";
            }
        }

        if ($this->onUpdateCurrent) {
            $sql .= " ON UPDATE CURRENT_TIMESTAMP";
        }

        return $sql;
    }
}
