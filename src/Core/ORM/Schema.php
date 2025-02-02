<?php

namespace Villeon\Core\ORM;

use Villeon\Core\ORM\Connectors\SQLConnector;

class Schema
{
    public static function drop(string $table, bool $cascade = false)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \InvalidArgumentException("Invalid table name.");
        }
        if ($cascade) {
            self::dropForeignKeys($table);
        }
        SQLConnector::of()->execute("DROP TABLE IF EXISTS `$table`");
        return true;
    }

    /**
     * Drop foreign key constraints related to the given table.
     *
     * @param string $table The name of the table to remove foreign keys for.
     */
    private static function dropForeignKeys(string $table)
    {
        $query = "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL";
        $foreignKeys = SQLConnector::of()->getAll($query);

        // Drop each foreign key constraint
        foreach ($foreignKeys as $fk) {
            SQLConnector::of()->execute("ALTER TABLE `$table` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
        }
    }

    public static function exits(string $table): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
            $result = SQLConnector::of()->getOne($query, [$table]);
            return (bool)$result["COUNT(*)"];
        } catch (\Throwable $e) {
            return false;
        }
    }
}
