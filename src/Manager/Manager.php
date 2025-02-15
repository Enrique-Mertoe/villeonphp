<?php

namespace Villeon\Manager;

use Villeon\Http\Request;

class Manager
{

    public static function modelExists($name): bool
    {
        $filePath = app_context()->getSrc() . "/Models" . '/' . ucfirst($name) . '.php';
        return file_exists($filePath);
    }

    public static function deleteModel($name): bool
    {
        $file = app_context()->getSrc() . DIRECTORY_SEPARATOR . "Models" . DIRECTORY_SEPARATOR . ucfirst($name) . '.php';
        return file_exists($file) && unlink($file);
    }

    private static function renameModel($old, $new): bool
    {
        $modelDir = app_context()->getSrc() . DIRECTORY_SEPARATOR . "Models";
        $old = $modelDir . DIRECTORY_SEPARATOR . ucfirst($old) . ".php";
        $new = $modelDir . DIRECTORY_SEPARATOR . ucfirst($new) . ".php";
        return rename($old, $new);
    }

    public static function createModel($name, $alias, $attributes, $alter = false): array
    {
        if ($alter) {
            $ini = Request::form("initials");
            $old = trim($ini["name"] ?? "");
            if (!empty($old) && $name !== $old && !self::renameModel($old, $name)) {
                return [false, "Could not modify Model from $old to $name"];
            }
        }

        $class = ucfirst($name);
        $modelDir = app_context()->getSrc() . DIRECTORY_SEPARATOR . "Models";

        if (!$alter && !mkdir($modelDir, 0755, true) && !is_dir($modelDir)) {
            return [false, "Failed to create directory: $modelDir"];
        }
        $filePath = $modelDir . DIRECTORY_SEPARATOR . $class . '.php';

        if (!$alter && file_exists($filePath)) {
            return [false, "Model \"<u>$class</u>\" already exists.\n"];
        }
        $modelContent = [];
        $modelConstruct = "";
        $modelVars = "";

        if ($alias && !$alter) {
            if (!str_ends_with($alias, 's')) {
                $alias .= 's';
            }
            $modelConstruct .= "\$table->table(\$this->tableName);" . "\n        ";
            $modelVars .= "public string \$tableName = \"$alias\";\n";
        }
        foreach ($attributes as $column) {
            $column = array_map(static function ($item) {

                return match (trim($item)) {
                    "true" => true,
                    "false" => false,
                    "null" => null,
                    default => $item
                };
            }, $column);
            $modelConstruct .= self::buildSchema($column) . "\n        ";
        }
        $modelConstruct = str($modelConstruct)->trimEnd()->trimEnd("\n");
        $modelContent = implode("", $modelContent);

        $classTemplate = <<<EOT
            <?php
            
            namespace App\Models;
            
            use Villeon\Core\ORM\FieldSchema;            
            use Villeon\Core\ORM\Model;
            
            class $class extends Model
            {
                $modelVars
                public function schema(FieldSchema \$table): void
                {
                    $modelConstruct
                }
            }            
            EOT;
        file_put_contents($filePath, $classTemplate);
        return [true, $filePath];
    }

    private static function buildSchema(array $column): string
    {
        $columnName = $column["name"];
        $type = $column["type"];
        $default = $column["defaultVal"];
        $primary = $column["primary"];
        $unique = $column["unique"];
        $nullable = $column["nullable"];
        $auto = $column["auto"];
        $len = null;
        $st = "";
        if ($columnName === "id")
            return "\$table->id();";
        switch ($type) {
            case "INT":
                $st .= "\$table->int(";
                break;
            case "TEXT":
                $st .= "\$table->string(";
                break;
            case "BOOLEAN":
                $st .= "\$table->bool(";
                break;
            case "VARCHAR":
                $len = 255;
                $st .= "\$table->string(";
                break;
            case "DATE";
                $st .= "\$table->date(";
                break;
        }
        $st .= "\"$columnName\"";
        if ($len !== null) {
            $st .= ", $len";
        }
        if (!empty($default)) {
            $st .= ", default: " . match ($type) {
                    "TEXT", "VARCHAR" => "\"$default\"",
                    "DATE" => $default === "now" ? "\"CURRENT_TIMESTAMP\"" : "\"$default\"",
                    "BOOLEAN" => (bool)$default,
                    "INT" => (int)$default,
                    default => $default
                };
        }
        if ($nullable) {
            $st .= ", null: true";
        }
        if ($primary) {
            $st .= ", primary: true";
        }
        if ($unique) {
            $st .= ", unique: true";
        }
        if ($auto) {
            $st .= ", auto: true";
        }
        $st .= ");";

        return $st;
    }
}

