<?php

namespace Villeon\Support\ControlPanel;

use Closure;
use Exception;
use Villeon\Core\Facade\Env;
use Villeon\Core\Facade\Settings;
use Villeon\Database\VilleonSQL\DataTypes\DataTypes;
use Villeon\Database\VilleonSQL\Model;
use Villeon\Database\VilleonSQL\VilleonSQL;
use Villeon\DB\DataBase;
use Villeon\DB\ModelHandler;
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

    public static function required_fns()
    {
        function_exists("new_model");
        function_exists("db_config");
        function_exists("create_super_admin");
        function_exists("components");
        function_exists("disable_secure");
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
        } catch (Exception $e) {
            return $this->make_res(data: $e->getMessage());
        }

    }

    /**
     * @return Response
     */
    public function new_model(): Response
    {
        [$name, $db_name, $cols] = array_values(Request::$form->array());
        $res = DataBase::createModel($name, $db_name, $cols);
        return $this->make_res(ok: $res === true, data: $res);

    }

    /**
     * @return Response
     */
    public function db_config(): Response
    {
        [$db_server, $db_user, $db_password, $db_name] = array_values(Request::$form->array());
        DataBase::configDbInfo($db_server, $db_user, $db_password, $db_name);
//        VilleonSQL::save_connection_string("$db_server//$db_user//$db_password//$db_name");
        return $this->make_res(ok: true);
    }

    public function disable_secure(): Response
    {
        Settings::set("SHOW_ADMIN_SECURE_WIZARD", false);
        return $this->make_res(ok: true);
    }

    public function create_super_admin(): Response
    {
        [$email, $password] = array_values(Request::$form->array());
        $model = Model::define("villeon_admin", [
            "email" => [
                "type" => DataTypes::STRING(),
                "unique" => true
            ],
            "password" => [
                "type" => DataTypes::STRING()
            ]
        ]);
        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $model->init_model();
        $model->create([
            "email" => $email,
            "password" => password_hash($password, $algorithm)
        ]);
        Settings::set("SHOW_ADMIN_SECURE_WIZARD", false);
        Settings::set("PANEL_SECURED", true);
        return $this->make_res(ok: true);
    }

    public function table_delete(): Response
    {
        [$table] = array_values(Request::$form->array());
        Model::removeModel($table);
        return $this->make_res(ok: true);
    }

    public function table_info(): Response
    {
        [$table] = array_values(Request::$form->array());
        Log::i("kkk", $table);
        $data = $this->view("comp/model_view.twig", ["model_info" => Model::infoSchema($table)]);
        return $this->make_res(ok: true, data: $data);
    }

    /**
     * @return Response
     */
    private function components(): Response
    {
        $info = [
            "defaultModelColumns" => [
                [
                    "colName" => "id",
                    "dataType" => "INT",
                    "primary" => true,
                    "unique" => false,
                    "auto" => true,
                    "nullable" => false,
                ],
                [
                    "colName" => "created_at",
                    "dataType" => "INT",
                    "primary" => false,
                    "unique" => false,
                    "auto" => false,
                    "nullable" => false,
                ]
            ]
        ];
        $view = $this->view("components.twig", $info);
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
     * @param array $arg
     * @return mixed
     */
    private function view(string $name, array $arg = []): mixed
    {
        return ($this->renderer)($name, $arg);
    }
}
