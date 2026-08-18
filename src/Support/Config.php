<?php
declare(strict_types=1);

namespace SiteForgeAI\Support;

class Config
{
    /**
     * In-memory cache of loaded config files.
     */
    private static array $cache = [];

    /**
     * Retrieve a configuration value using dot notation.
     * Example: Config::get('defaults.ai_model') or Config::get('presets')
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file     = array_shift($segments);

        // Load and cache the configuration file
        if (!isset(self::$cache[$file])) {
            $path = SITEFORGE_AI_DIR . 'src/Config/' . $file . '.php';

            if (!file_exists($path)) {
                return $default;
            }

            self::$cache[$file] = require $path;
        }

        $target = self::$cache[$file];

        // If only the filename was requested, return full array
        if (empty($segments)) {
            return $target;
        }

        // Traverse nested segments (e.g. 'ai_models.providers.openai')
        foreach ($segments as $segment) {
            if (!is_array($target) || !array_key_exists($segment, $target)) {
                return $default;
            }
            $target = $target[$segment];
        }

        return $target;
    }
}
