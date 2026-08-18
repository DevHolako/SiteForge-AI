<?php

use SiteForgeAI\Core\Router;
use SiteForgeAI\Controllers\SettingsController;

test('it resolves classes and auto-wires constructor dependencies via Reflection', function () {
    $controller = Router::resolve(SettingsController::class);

    expect($controller)
        ->toBeInstanceOf(SettingsController::class);
});