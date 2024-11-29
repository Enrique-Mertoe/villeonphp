<?php

namespace Villeon\Support\ControlPanel;

use Closure;
use Villeon\Database\VilleonSQL\Model;
use Villeon\Database\VilleonSQL\VilleonSQL;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Utils\Log;

/**
 *
 */
class ActionBuilder
{
    /**
     *
     */
    private const TAG = "CONTROL_PANEL";
    /**
     * @var
     */
    private $action;


    /**
     * @var
     */
    private $renderer;

    /**
     * @param $action
     * @param $renderer
     */
    public function __construct($action, $renderer)
    {
        $this->action = $action;
        $this->renderer = $renderer;

    }

    /**
     * @param $action
     * @param $renderer
     * @return mixed
     */
    public static function get($action, $renderer): mixed
    {
        return (new ActionBuilder($action, $renderer))->process_action();
    }

    /**
     * @return Response
     */
    private function process_action(): Response
    {
        if (!method_exists($this, $this->action))
            return $this->make_res();
        $action = $this->action;
        try {
            return $this->$action();
        } catch (\Exception $e) {
            return $this->make_res(data: $e->getMessage());
        }

    }

    /**
     * @return Response
     */
    public function new_model(): Response
    {

        [$name, $db_name] = array_values(Request::$form->array());
        Model::define(ucfirst($name))->init_model();
        return $this->make_res(ok: true);

    }

    /**
     * @return Response
     */
    public function db_config(): Response
    {
        [$db_server, $db_user, $db_password, $db_name] = array_values(Request::$form->array());
        VilleonSQL::save_connection_string("$db_server//$db_user//$db_password//$db_name");
        return $this->make_res(ok: true);
    }

    /**
     * @return Response
     */
    private function components(): Response
    {
        $view = $this->view("components.twig");
        return $this->make_res(ok: true, data: $view);
    }

    /**
     * @param bool $ok
     * @param $data
     * @return Response
     */
    private function make_res(bool $ok = false, $data = null): Response
    {
        return jsonify(["ok" => $ok, "data" => $data]);
    }

    /**
     * @param string $name
     * @param ...$arg
     * @return mixed
     */
    private function view(string $name, array $arg = []): mixed
    {
        return ($this->renderer)($name, $arg);
    }
}