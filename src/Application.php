<?php
/**
 * Application.php
 *
 * This file contains the implementation of the Application class,
 * which manages the entire .
 *
 * @package    Villeon\Manager
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon;


use Villeon\Config\ConfigBuilder;
use Villeon\Core\ApplicationBuilder;
use Villeon\Core\Content\AppContext;
use Villeon\Core\Content\ContentManager;
use Villeon\Core\Content\AppCombat;
use Villeon\Core\Facade\Config;
use Villeon\Core\VilleonBuilder;
use Villeon\Database\VilleonSQL\Connection\DBOptions;
use Villeon\Database\VilleonSQL\VilleonSQL;


//final class Application extends ApplicationBuilder
//{
//    /**
//     * @return void
//     */
//    public function create(): void
//    {
//        VilleonSQL::init_database(
//            new DBOptions(
//                host: env("DB_SERVER", ''),
//                user: env("DB_USER", ''),
//                password: env("DB_PASSWORD", ''),
//                name: env("DB_NAME", '')
//            )
//        );
//        (new VilleonSQL())->build();
//        $this->load_extensions();
//        $this->app->build();
//
//    }
//
//    public function withConfig(callable $callback): Application
//    {
//        $callback();
//        Config::load_module("views");
////        Config::load_module("models");
//        return $this;
//    }
//}

final class Application extends ContentManager
{

    public static function new(string $basePath): Application
    {
        $inst = new Application;
        $inst->initApp($basePath);
        return $inst;
    }

    protected function initApp(string $root): void
    {
        $this->appCombat = new AppCombat($root);
        $this->appCombat->initApp($this);
        $this->loadComponents();
        $this->build();
    }

}
