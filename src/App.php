<?php

namespace Villeon;

use Villeon\Config\ConfigLoader;
use Villeon\Container\Container;

/**
 * The `Application` class serves as the main entry point for managing the framework.
 * It handles essential configurations, dependency management, and initialization.
 *
 * @package Villeon\Manager
 */
final class App
{
    /**
     * The base path of the application.
     *
     * @var string $basePath The root directory of the application.
     */
    private string $basePath;

    /**
     * The application's dependency injection container.
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * The current environment of the application.
     *
     * @var string $environment
     */
    private string $environment;

    /**
     * Singleton instance of the `Application` class.
     *
     * @var static|null $instance
     */
    private static ?App $instance = null;

    /**
     * Private constructor to enforce singleton pattern.
     *
     * @param string $basePath The root directory of the application.
     */
    private function __construct(string $basePath)
    {
        $this->basePath = $basePath;
//        $this->container = new Container();
    }

    /**
     * Creates or retrieves the singleton instance of the `Application` class.
     *
     * @param string $basePath The root directory of the application.
     * @return static The singleton instance of the application.
     */
    public static function getInstance(string $basePath = ''): static
    {
        if (self::$instance === null) {
            self::$instance = new static($basePath);
        }
        return self::$instance;
    }

    /**
     * Gets the base path of the application.
     *
     * @return string The base path.
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Sets the base path of the application.
     *
     * @param string $basePath The new base path.
     * @return void
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * Loads the configuration for the application.
     *
     * @param string $configPath The path to the configuration files.
     * @return void
     */
    public function loadConfig(string $configPath): void
    {
//        ConfigLoader::load($this->container, $configPath);
    }

    /**
     * Gets the current environment of the application.
     *
     * @return string The current environment.
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Sets the current environment of the application.
     *
     * @param string $environment The environment name (e.g., 'development', 'production').
     * @return void
     */
    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * Registers a service provider in the application.
     *
     * @param string $providerClass The service provider class name.
     * @return void
     */
    public function registerServiceProvider(string $providerClass): void
    {
        $provider = new $providerClass($this->container);
        $provider->register();
    }

    /**
     * Boots the application by running startup tasks.
     *
     * @return void
     */
    public function boot(): void
    {
        // Example: Load environment variables, initialize services, etc.
        echo "Application booted successfully!";
    }
}
