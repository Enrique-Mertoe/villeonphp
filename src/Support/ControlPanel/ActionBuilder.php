<?php

namespace Villeon\Support\ControlPanel;

use Closure;
use Villeon\Http\Response;

class ActionBuilder
{
    private $action;


    private $renderer;

    public function __construct($action, $renderer)
    {
        $this->action = $action;
        $this->renderer = $renderer;

    }

    public static function get($action, $renderer): mixed
    {
        return (new ActionBuilder($action, $renderer))->process_action();
    }

    private function process_action()
    {
        if (!method_exists($this, $this->action))
            return $this->get_res();
        $action = $this->action;
        return $this->$action();

    }

    private function components(): Response
    {
        $view = $this->view("components.twig");
        return $this->get_res(ok: true, data: $view);
    }

    private function get_res($ok = false, $data = null): Response
    {
        return jsonify(["ok" => $ok, "data" => $data]);
    }

    private function view(string $name, ...$arg)
    {
        return ($this->renderer)($name, ...$arg);
    }
}