<?php

declare(strict_types=1);

use SiteForgeAI\Core\Route;
use SiteForgeAI\Controllers\SeederController;

Route::post('seeder/run', SeederController::class, 'run');
