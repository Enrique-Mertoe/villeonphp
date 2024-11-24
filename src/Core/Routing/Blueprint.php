<?php

namespace Villeon\Core\Routing;

/**
 * The Blueprint class is responsible for defining and managing route groupings
 * in the application, allowing for better organization of routes.
 * It extends the Router class and provides functionality to set route groups
 * and URL prefixes for those groups.
 *
 * This class can be used to group routes with common attributes such as a
 * URL prefix and a shared name.
 *
 * @package Villeon\Core\Routing
 */
final class Blueprint extends Router
{
    /**
     * Blueprint constructor to initialize a route group with a name and an optional URL prefix.
     * This is used to define a set of routes that share the same attributes, such as
     * a common URL prefix or group name.
     *
     * @param string $name The name of the route group (used for identification).
     * @param string|null $url_prefix The URL prefix to apply to all routes within the group (optional).
     */
    public function __construct(string $name, ?string $url_prefix)
    {
        $this->name = $name;
        $this->prefix = $url_prefix;
        parent::__construct($name);
    }

    /**
     * A static method to create and return a new instance of the Blueprint class.
     * This method allows for easy and descriptive creation of Blueprint objects.
     *
     * @param string $name The name of the route group to be defined.
     * @param string|null $url_prefix The optional URL prefix to apply to the group's routes.
     * @return Blueprint A new instance of the Blueprint class.
     */
    public static function define(string $name, ?string $url_prefix = null): Blueprint
    {
        return new Blueprint($name, $url_prefix);
    }
}