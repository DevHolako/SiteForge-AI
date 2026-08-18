<?php

declare(strict_types=1);

// Load WordPress environment if available
$wp_load = 'c:/xampp/htdocs/wordpress/wp-load.php';
if (file_exists($wp_load)) {
    require_once $wp_load;
}

// Autoload plugin code
require_once __DIR__ . '/../Core/Autoloader.php';
\SiteForgeAI\Core\Autoloader::register();
require_once __DIR__ . '/../Support/helpers.php';

// Helper to authenticate as admin in test requests
function authenticateAsAdmin(): void
{
    if (function_exists('wp_set_current_user')) {
        wp_set_current_user(1);
    }
}

// Bind Tia State cleanly in userland container bootstrap
if (class_exists(\Pest\Support\Container::class) && interface_exists(\Pest\Plugins\Tia\Contracts\State::class)) {
    $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pest_tia_' . md5(__DIR__);
    \Pest\Support\Container::getInstance()->add(
        \Pest\Plugins\Tia\Contracts\State::class,
        new \Pest\Plugins\Tia\FileState($tempDir)
    );
}