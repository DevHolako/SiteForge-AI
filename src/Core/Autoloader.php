<?php

declare(strict_types=1);

namespace SiteForgeAI\Core;

/**
 * Class Autoloader
 * @package SiteForgeAI\Core
 */

class Autoloader
{
    private const  PREFIX = 'SiteForgeAI\\';

    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        // 1. Does the class start with our plugin's root prefix?
        if (!str_starts_with($class, self::PREFIX)) {
            return;
        }


        // 2. Strip 'SiteForgeAI\' -> leaves 'Core\Plugin' or 'Http\Router'
        $relative_class = substr($class, strlen(self::PREFIX));

        // 3. Replace '\' with '/' -> 'Core/Plugin'
        $path = str_replace('\\', '/', $relative_class);

        // 4. Build absolute path -> .../src/Core/Plugin.php
        $file = SITEFORGE_AI_DIR . 'src/' . $path . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }

}
