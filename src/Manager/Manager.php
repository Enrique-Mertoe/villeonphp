<?php

namespace Villeon\Manager;

use Villeon\Manager\Handlers\ModelProcessor;

class Manager
{
    /**
     * Retrieves all model classes from the Models directory.
     *
     * @param string $namespace The namespace prefix for the models.
     *
     * @return array An array of fully qualified model class names.
     */
    public static function getModels(string $namespace = "App\\Models\\"): array
    {
        $modelsPath = app_context()->getSrc() . "/Models";
        $models = [];
        if (!is_dir($modelsPath) && !mkdir($modelsPath, 0755, true) && !is_dir($modelsPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $modelsPath));
        }

        foreach (glob($modelsPath . '/*.php') as $file) {
            require_once $file;
            $className = pathinfo($file, PATHINFO_FILENAME);
            $fullClassName = $namespace . $className;
            if (class_exists($fullClassName)) {
                $models[] = ModelProcessor::of($fullClassName)->process();
            }
        }
        return $models;
    }

    public static function modelExists($name): bool
    {
        $filePath = app_context()->getSrc() . "/Models" . '/' . ucfirst($name) . '.php';
        return file_exists($filePath);
    }

    public static function deleteModel($name): void
    {
        $file = app_context()->getSrc() . "/Models" . '/' . ucfirst($name) . '.php';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public static function createModel($name, $alias, $attributes): bool|string
    {
        $class = ucfirst($name);
        $modelDir = app_context()->getSrc() . "/Models";

        if (!is_dir($modelDir) && !mkdir($modelDir, 0755, true) && !is_dir($modelDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $modelDir));
        }
        $filePath = $modelDir . '/' . $class . '.php';
        if (file_exists($filePath)) {
            return "Model \"<u>$class</u>\" already exists.\n";
        }
        $modelContent = [];
        $modelConstruct = "";

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
            $modelConstruct .= self::buildSchema($column) . "\n        ";
        }
        $modelConstruct = str($modelConstruct)->trimEnd()->trimEnd("\n");
        $modelContent = implode("", $modelContent);

        $classTemplate = <<<EOT
            <?php
            
            namespace App\Models;
            
            use Villeon\Core\ORM\DBSchema;            
            use Villeon\Core\ORM\Model;
            
            class $class extends Model
            {
            $modelContent
                public function schema(DBSchema \$table): void
                {
                    $modelConstruct
                }
            }            
            EOT;
        file_put_contents($filePath, $classTemplate);
        return true;
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
            $st .= ", default: $default";
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
