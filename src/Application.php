<?php
/**
 * Application.php
 *
 * This file contains the implementation of the `Application` class, which serves as the entry point to the application.
 * It is responsible for initializing the application, setting up required components, and starting the application lifecycle.
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

/**
 * The `Application` class manages the entire application lifecycle.
 * It initializes core components, handles requests, and dispatches events.
 *
  */
final class Application extends ContentManager
{
    /**
     * Creates a new instance of the `Application` class and initializes it with the provided base path.
     *
     * @param string $basePath The root directory path of the application.
     * @return Application Returns the newly created `Application` instance.
     */
    public static function new(string $basePath): Application
    {
        $inst = new Application;
        $inst->initApp($basePath);
        return $inst;
    }

    /**
     * Initializes the application by setting up the `AppCombat` instance and loading necessary components.
     *
     * This method is automatically called when the application is instantiated via the `new()` method.
     *
     * @param string $root The root directory path for initializing the application.
     * @return void
     */
    protected function initApp(string $root): void
    {
        $this->appCombat = new AppCombat($root);
        $this->appCombat->initApp($this);  // Initialize AppCombat with the current Application instance.
        $this->loadComponents();           // Load essential components like facades.
        $this->build();                    // Build and configure the application.
    }
}
