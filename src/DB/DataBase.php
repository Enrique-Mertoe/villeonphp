<?php

namespace Villeon\DB;

use Villeon\Core\Facade\Env;
use Villeon\Core\Internal\Settings;

class DataBase
{
    public static function createModel($name, ?string $tableName = null, ?array $attributes = null): string|bool
    {
        $model = ModelHandler::define($name, $tableName, $attributes);
        return $model->create();
    }

    public static function configDbInfo(
        string $host,
        string $user,
        string $password,
        string $name,
    ): void
    {

        Settings::getInstance()->update(["DB_SERVER" => trim($host) ?: "localhost",
            "DB_USER" => trim($user) ?: "root",
            "DB_PASSWORD" => trim($password) ?: "",
            "DB_NAME" => trim($name) ?: "",]);
    }

    private static function get_conn_value($var, $default)
    {
        $var = trim($var);
        return !$var ? $default : $var;
    }
}
