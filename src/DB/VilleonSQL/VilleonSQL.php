<?php

namespace Villeon\Database\VilleonSQL;

use Villeon\Database\VilleonSQL\Connection\DBOptions;

/**
 *
 */
class VilleonSQL
{
    /**
     * @var DBOptions|null
     */
    static ?DBOptions $options = null;

    /**
     * @param DBOptions $options
     * @return void
     */
    public static function init_database(DBOptions $options): void
    {
        self::$options = $options;
    }

    /**
     * @return void
     */
    public function build(): void
    {
//        Model::init_model();
    }

    /**
     * @param string $conn
     * @return void
     */
    public static function save_connection_string(string $conn): void
    {
        $file = BASE_PATH . "/.env";
        $e_content = explode("\n", file_get_contents($file));
        [$_s, $_u, $_p, $_n] = explode("//", $conn);
        $out = [];

        foreach ($e_content as $line) {
            $line = trim($line);
            if (!preg_match('/^[#;]?\s*(DB_\w+)/', $line)) {
                $out[] = $line;
            }
        }
        if (end($out))
            $out[] = "";
        $out[] = "DB_SERVER = " . self::get_conn_value($_s, "localhost");
        $out[] = "DB_USER = " . self::get_conn_value($_u, 'root');
        $out[] = "DB_PASSWORD = " . $_p;
        $out[] = "DB_NAME = " . self::get_conn_value($_n, "villeon_db");

        file_put_contents($file, implode("\n", $out));
    }

    /**
     * @param $var
     * @param $default
     * @return mixed|string
     */
    private static function get_conn_value($var, $default)
    {
        $var = trim($var);
        return !$var ? $default : $var;
    }
}
