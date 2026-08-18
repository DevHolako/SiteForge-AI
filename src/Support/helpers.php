<?php
declare(strict_types=1);

use SiteForgeAI\Support\Config;

if (!function_exists('siteforge_config')) {
    /**
     * Helper function to get config values using dot notation.
     * Example: siteforge_config('defaults.ai_model')
     */
    function siteforge_config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}
