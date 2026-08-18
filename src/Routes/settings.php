<?php

declare(strict_types=1);

use SiteForgeAI\Core\Route;
use SiteForgeAI\Controllers\SettingsController;

Route::get('settings/get', SettingsController::class, 'get');
Route::post('settings/save', SettingsController::class, 'save');
Route::get('presets/list', SettingsController::class, 'presets');
