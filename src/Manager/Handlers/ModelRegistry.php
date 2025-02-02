<?php

namespace Villeon\Manager\Handlers;

use Villeon\Core\ORM\Connectors\SQLConnector;
use Villeon\Core\ORM\Models\QRYBuilder;
use Villeon\Core\ORM\Schema;
use Villeon\Support\ControlPanel\PanelHandler;
use Villeon\Support\ControlPanel\RequestHandler;

class ModelRegistry extends PanelHandler
{
    /**
     * @var ModelDef[]
     */
    private static array $models;

    public static function getModels(bool $refresh = false): array
    {
        if (empty(self::$models) || $refresh) {
            self::$models = self::loadModels();
        }
        return self::$models;
    }

    public static function deleteModel($name, $table): array
    {
        Schema::drop($table, true);
        $file = app_context()->getSrc() . "/Models" . '/' . ucfirst($name) . '.php';
        if (file_exists($file)) {
            unlink($file);
        }
        return [true, "Model $name deleted."];
    }

    public static function modelInfo($name, $table): array
    {
        $file = app_context()->getSrc() . "/Models" . '/' . ucfirst($name) . '.php';
        if (!file_exists($file)) {
            return [false, "Model not found"];
        }
        require_once $file;
        $class = "APP\\Models\\" . pathinfo($file, PATHINFO_FILENAME);
        if (!class_exists($class)) {
            return [false, "Model not implemented"];
        }
        $model = ModelProcessor::of($class)->info();
        $model = (RequestHandler::$renderer)("comp/model-edit.twig", ["model" => $model]);
        return [true, $model];
    }

    public static function syncModels(): array
    {
        foreach (self::getModels() as $model) {
            if (!$model->exits) {
                $sql = QRYBuilder::from($model->schema, $model->exits);
                SQLConnector::of()->execute($sql);
            }
        }
        return [0, "Models synchronization successful!"];
    }

    public static function loadModels(string $namespace = "App\\Models\\"): array
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
                $models[] = ModelProcessor::of($fullClassName)->info();
            }
        }
        return $models;
    }

    public static function actionBuilder(): \Closure
    {
        return static fn($name, $args) => self::action($name, $args);
    }

    public static function action(string $name, array $args): mixed
    {
        $action = match ($name) {
            "del" => "deleteModel",
            "sync" => "syncModels",
            "info" => "modelInfo",
            default => $name
        };
        if (!method_exists(self::class, $action)) {
            return [0, "Invalid action: '$name'"];
        }
        try {
            return self::$action(...$args);
        } catch (\Throwable $e) {
            return [0, $e->getMessage()];
        }
    }
}
