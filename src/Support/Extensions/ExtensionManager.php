<?php

namespace Villeon\Support\Extensions;

use Villeon\Core\Facade\Facade;

class ExtensionManager
{
    /**
     * @var array<string, ExtensionBuilder>
     */
    private array $resolvedInstances;
    private static ExtensionManager $manager;

    /**
     *
     */
    public function __construct()
    {
        $this->resolvedInstances = [];
    }

    /**
     * @return void
     */
    public static function init(): void
    {
        self::$manager = new static();
        Facade::setInstance("extension", self::$manager);
    }

    public static function load_all()
    {
        foreach (self::$manager->resolvedInstances as $instance) {
            if ($instance->isEnabled()) {
                $instance->build();
            }
        }
    }

    /**
     * @param string $extension_name
     * @param ExtensionBuilder $builder
     * @param bool $enabled
     * @return void
     */
    public function add(string $extension_name, ExtensionBuilder $builder, bool $enabled = true): void
    {
        $builder->setEnabled($enabled);
        $this->resolvedInstances[$extension_name] = $builder;
    }

    /**
     * @param string $extension_name
     * @return ExtensionBuilder|null
     */
    public function get(string $extension_name): ExtensionBuilder|null
    {
        return null;
    }

    /**
     * @param string $extension_name
     * @param bool $enabled
     * @return void
     */
    public function enabled(string $extension_name, bool $enabled)
    {

    }
}