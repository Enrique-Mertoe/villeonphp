<?php

namespace Villeon\Support\ControlPanel;

use Closure;
use Exception;
use Villeon\Http\Request;
use Villeon\Manager\Handlers\ModelRegistry;

class RequestHandler
{
    public static array $renderer;

    public function __construct($r)
    {
        self::$renderer = $r;
    }

    public function get(): array
    {
        $target = Request::form("target");
        $action = Request::form("action");
        if (empty($target) || empty($action)) {
            return $this->response(data: "Invalid target and action combination $target $action");
        }
        $target = match ($target) {
            "models" => ModelRegistry::actionBuilder(),
            default => $this->actionBuilder()
        };
        $args = Request::form("args") ?? [];
        return $this->response(...$target($action, $args));

    }

    private function actionBuilder(): Closure
    {
        return static fn($name, $args) => self::action($name, $args);
    }

    public static function action(string $name, array $args): mixed
    {
        try {
            return self::$name(...$args);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function response($ok = false, $data = null, ...$options): array
    {
        return ["ok" => $ok, "data" => $data, ...$options];
    }
}
