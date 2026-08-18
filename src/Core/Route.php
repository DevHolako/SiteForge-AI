<?php

declare(strict_types=1);

namespace SiteForgeAI\Core;

class Route
{
    /**
     * Internal registry of all defined routes.
     */
    private static array $routes = [];

    /**
     * Register a GET route.
     */
    public static function get(string $path, string $controller, string $action, ?callable $permission = null): void
    {
        self::add('GET', $path, $controller, $action, $permission);
    }

    /**
     * Register a POST route.
     */
    public static function post(string $path, string $controller, string $action, ?callable $permission = null): void
    {
        self::add('POST', $path, $controller, $action, $permission);
    }

    /**
     * Register a DELETE route.
     */
    public static function delete(string $path, string $controller, string $action, ?callable $permission = null): void
    {
        self::add('DELETE', $path, $controller, $action, $permission);
    }

    /**
     * Internal helper to store the route definition.
     */
    private static function add(string $method, string $path, string $controller, string $action, ?callable $permission = null): void
    {
        self::$routes[ltrim($path, '/')] = [
            'methods'             => $method,
            'controller'          => $controller,
            'action'              => $action,
            'permission_callback' => $permission ?? [\SiteForgeAI\Security\Permission::class, 'checkAdmin'],
        ];
    }

    /**
     * Get all registered routes.
     */
    public static function all(): array
    {
        return self::$routes;
    }
}
