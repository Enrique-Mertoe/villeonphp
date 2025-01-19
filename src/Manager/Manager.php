<?php

namespace Villeon\Manager;

class Manager
{
    public static function createModel($name, $alias, $attributes): bool|string
    {
        $class = ucfirst($name);
        $modelDir = app_context()->getSrc() . "/models";

        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }
        $filePath = $modelDir . '/' . $class . '.php';
        if (file_exists($filePath)) {
            return "Model '$class' already exists.\n";
        }
        $modelContent = [];
        if ($alias)
            $modelContent[] = "    private string \$tableName = \"$alias\";\n";
        foreach ($attributes as $column) {
            $columnName = $column['name'];
            $columnType = static::mapColumnType($column);
            $modelContent[] = "    public $columnType \$$columnName;\n";
        }
        $modelContent = implode("", $modelContent);

        $classTemplate = <<<EOT
            <?php
            
            namespace App\Models;
            
            use Villeon\Core\ORM\Models\Model;
            
            class $class extends Model
            {
            $modelContent
            }            
            EOT;
        file_put_contents($filePath, $classTemplate);
        return true;
    }

    private static function mapColumnType($column): string
    {
        if ($column['primary']) {
            return 'int';
        } elseif ($column['auto']) {
            return 'int';
        } elseif ($column['type'] == 'Date') {
            return 'string';
        } else {
            return 'string';
        }
    }
}
