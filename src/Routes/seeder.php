<?php

declare(strict_types=1);

use SiteForgeAI\Core\Route;
use SiteForgeAI\Controllers\SeederController;

Route::post('seeder/seed', SeederController::class, 'seed');
Route::post('seeder/run', SeederController::class, 'seed');
Route::post('seeder/remove-mock', SeederController::class, 'removeMock');
