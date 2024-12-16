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

use Villeon\Core\Content\ContentManager;
use Villeon\Core\Content\AppCombat;

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
