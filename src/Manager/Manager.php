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
            return "Model \"<u>$class</u>\" already exists.\n";
        }
        $modelContent = [];
        $modelConstruct = [];
        if ($alias)
            $modelContent[] = "    private string \$tableName = \"$alias\";\n";
        foreach ($attributes as $column) {
            $column = array_map(function ($item) {

                return match (trim($item)) {
                    "true" => true,
                    "false" => false,
                    "null" => null,
                    default => $item
                };
            }, $column);
            $columnName = $column["name"];
            $type = $column["type"];
            $default = $column["defaultVal"];
            $primary = $column["primary"];
            $unique = $column["unique"];
            $nullable = $column["nullable"];
            $auto = $column["auto"];
            $type = static::mapColumnType($type);
            $modelContent[] = "    public $type[0] \$$columnName;\n";
            $pars = [];
            if ($default)
                $pars[] = "default: \"$default\"";

            if ($primary)
                $pars[] = "isPrimary: true";
            if ($unique)
                $pars[] = "isUnique: true";
            if ($nullable)
                $pars[] = "allowNull: true";
            if ($auto)
                $pars[] = "autoValue: true";
            $pars = implode(", ", $pars);
            if ($pars)
                $pars = ", $pars";
            $modelConstruct[] = "\"$columnName\" => new ColField($type[1]$pars)";
        }
        $modelContent = implode("", $modelContent);
        $modelConstruct = "[\n            "
            . implode(",\n            ", $modelConstruct) . "\n        ]";

        $classTemplate = <<<EOT
            <?php
            
            namespace App\Models;
            
            use Villeon\Core\ORM\DataTypes\DataType;
            use Villeon\Core\useVilleon\Core\ORM\ColField;
            
            use Villeon\Core\ORM\Models\Model;
            
            class $class extends Model
            {
            $modelContent
                protected function getAttributes(): array
                {
                    return $modelConstruct;
                }
            }            
            EOT;
        file_put_contents($filePath, $classTemplate);
        return true;
    }

    private static function mapColumnType($type): array
    {
        return match (strtoupper($type)) {
            "INT" => ["int", "DataType::INT"],
            "BOOLEAN" => ["bool", "DataType::BOOL"],
            "TEXT" => ["string", "DataType::STRING"],
            "VARCHAR" => ["string", "DataType::STRING()"],
            "DATE" => ["", "DataType::DATE"],
            default => throw new \RuntimeException("invalid datatype")
        };
    }

    static function formatCode($code)
    {
        $f = new PHPCodeFormatter();
        return $f->formatFile($code);
    }
}

class PHPCodeFormatter
{
    /**
     * Format a PHP file and return the formatted content.
     *
     * @param string $filePath Path to the PHP file.
     * @return string|bool Formatted PHP code, or false if the file does not exist.
     */
    public function formatFile(string $filePath)
    {
//        if (!file_exists($filePath)) {
//            return false;
//        }
//
//        $code = file_get_contents($filePath);
        return $this->formatCode($filePath);
    }

    /**
     * Format PHP code and return the formatted content.
     *
     * @param string $code PHP code as a string.
     * @return string Formatted PHP code.
     */
    public function formatCode(string $code): string
    {
        $tokens = token_get_all($code);
        $formattedCode = "";
        $indentLevel = 0;
        $newLine = true;
        $lastTokenWasNamespace = false;
        $lastTokenWasUse = false;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                list($id, $text) = $token;

                switch ($id) {
                    case T_NAMESPACE:
                        if ($formattedCode !== "") {
                            $formattedCode .= "\n";
                        }
                        $formattedCode .= $text . " ";
                        $lastTokenWasNamespace = true;
                        $newLine = false;
                        break;

                    case T_USE:
                        if ($lastTokenWasNamespace) {
                            $formattedCode .= "\n";
                        }
                        $formattedCode .= $text . " ";
                        $lastTokenWasUse = true;
                        $lastTokenWasNamespace = false;
                        $newLine = false;
                        break;

                    case T_CLASS:
                        if ($lastTokenWasUse) {
                            $formattedCode .= "\n"; // Add two new lines after use blocks
                        }
                        $formattedCode .= $text . " ";
                        $newLine = false;
                        break;

                    case T_FUNCTION:
                        $formattedCode .= "\n\n" . str_repeat("    ", $indentLevel) . $text . " ";
                        $newLine = false;
                        break;

                    case T_WHITESPACE:
                        if (!$newLine) {
                            $formattedCode .= " ";
                        }
                        break;

                    default:
                        if ($newLine) {
                            $formattedCode .= str_repeat("    ", $indentLevel);
                        }
                        $formattedCode .= $text;
                        $newLine = false;
                        break;
                }
            } else {
                // Handle single-character tokens (e.g., brackets, semicolons)
                if ($token === '{') {
                    $formattedCode .= "\n" . str_repeat("    ", $indentLevel) . "{\n";
                    $indentLevel++;
                    $newLine = true;
                } elseif ($token === '}') {
                    $indentLevel--;
                    $formattedCode .= "\n" . str_repeat("    ", $indentLevel) . "}\n";
                    $newLine = true;
                } elseif ($token === ';') {
                    $formattedCode .= ";\n";
                    $newLine = true;
                } else {
                    if ($newLine) {
                        $formattedCode .= str_repeat("    ", $indentLevel);
                    }
                    $formattedCode .= $token;
                    $newLine = false;
                }
            }
        }


        return preg_replace("/\n{3,}/", "\n\n", $formattedCode); // Limit consecutive newlines to two
    }
}
