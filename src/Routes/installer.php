<?php

declare(strict_types=1);

use SiteForgeAI\Core\Route;
use SiteForgeAI\Controllers\InstallerController;

Route::post('installer/install', InstallerController::class, 'install');
