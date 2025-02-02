<?php

namespace Villeon\Core\ORM\DataType;

class EnumDataType extends DataType
{
    private array $values;

    /**
     * @param array $values The allowed values for the ENUM column.
     */
    public function __construct(array $values)
    {
        parent::__construct('ENUM', 'enum');
        $this->values = $values;
    }

    /**
     * Converts the ENUM definition to SQL syntax.
     *
     * @param mixed|null $default
     * @return string The SQL definition for an ENUM column.
     */
    public function toSql(mixed $default = null): string
    {
        $escapedValues = array_map(static fn($value) => "'" . addslashes($value) . "'", $this->values);
        return "ENUM(" . implode(", ", $escapedValues) . ")" . ($default !== null) ? " DEFAULT '$default'" : "";
    }

    /**
     * Get the allowed ENUM values.
     *
     * @return array The list of values for the ENUM column.
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
