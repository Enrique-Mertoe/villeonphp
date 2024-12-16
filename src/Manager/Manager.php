<?php

namespace Villeon\Manager;

class Manager
{
    public static function createModel($name, $alias): void
    {
        $class = ucfirst($name);
        $modelDir = app_context()->getSrc() . "/models";

        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }
        $filePath = $modelDir . '/' . $class . '.php';
        if (file_exists($filePath)) {
            echo "Model '$class' already exists.\n";
            return;
        }

        $classTemplate = <<<EOT
            <?php
            
            namespace App\Models;
            
            use Villeon\Core\ORM\Models\Model;
            
            class $class extends Model
            {
                // Define table name if different from the default (optional)
                // protected \$table = 'your_table_name';
            
                // Add fillable properties if needed
                // protected \$fillable = ['column1', 'column2'];
            
                // Add relationships, accessors, or methods here
            }            
            EOT;
        file_put_contents($filePath, $classTemplate);

        echo "Model '$class' has been created successfully in $modelDir.\n";
    }
}
